<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Libraries\Helpers as CustomHelper;

class GlobalGradingController extends Controller
{

    public function __construct(\App\User $user, \App\Admin $admin, \App\GlobalGradingSets $globalGradingSets, \App\GlobalGradingSetValues $globalGradingSetValues){
        $this->user = $user;
        $this->admin = $admin;
        $this->globalGradingSets = $globalGradingSets;
        $this->globalGradingSetValues = $globalGradingSetValues;
        $this->dateFormat=config('constants.date_format');
        $this->dateTimeFormat=config('constants.date_time_format');
    }

    /**
    * Fetching all Global grading sets Method 
    * Return : all Global grading sets 
    **/
    public function allGlobalGradeingSets(Request $request){
    	$returnArr=config('constants.return_array');
    	$dataPerPage=config('constants.data_per_page');
    	$is_paginate=config('constants.is_paginate');
    	//$input=$request->all();
    	$input=array_map('trim', $request->all());

    	try{
    		if(isset($input['is_paginate']) && $input['is_paginate'] == 0){
	            $is_paginate=$input['is_paginate'];
	        }
    		if(isset($input['data_per_page']) && $input['data_per_page']!=""){
	            $dataPerPage=$input['data_per_page'];
	        }

	        if($is_paginate == 0){
	        	$globalGradingSetData=$this->globalGradingSets
	        				->with('globalgradingsetvalues')
	    					->get();
	    		$dataArray['data']=$globalGradingSetData->toArray();
	        }else{
	        	$globalGradingSetData=$this->globalGradingSets
	        				->with('globalgradingsetvalues')
	    					->paginate($dataPerPage);
	    		$dataArray=$globalGradingSetData->toArray();
	        }
	        

	    	if(isset($dataArray['data']) && !empty($dataArray['data'])){

	    		if($is_paginate == 0)
	    			$response=$dataArray;
	    		else{
	    			$response=[
		                'data' => $dataArray['data'],
		                'total' => $dataArray['total'],
		                'limit' => $dataArray['per_page'],
		                'pagination' => [
		                    'next_page' => $dataArray['next_page_url'],
		                    'prev_page' => $dataArray['prev_page_url'],
		                    'current_page' => $dataArray['current_page'],
		                    'first_page' => $dataArray['first_page_url'],
		                    'last_page' => $dataArray['last_page_url']
		                ]
		            ];
	    		}
		            

	            $returnArr['status']=2;
	            $returnArr['content']=$response;
	            $returnArr['message']="Data fetched successfully";
	        }else{
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No data found";
	        }
    	}
        catch(\Exception $e){
        	$returnArr['status']=6;
	        $returnArr['content']=$e;
	        $returnArr['message']="Something went wrong";
        }

        return $returnArr; 
    }

    /**
    * view Global grading set Method
    * view Global grading set information by it's ID
    * Return : a Global grading set's informations
    **/
    public function viewGlobalGradingSet(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		//$input=$request->all();
		$input=array_map('trim', $request->all());
		try{
			$validationRules=[
	            'global_grading_sets_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, global grading sets id not provided";
	            return $returnArr;
	        }

		    $globalGradingSetData=$this->globalGradingSets->where('global_grading_sets_id', $input['global_grading_sets_id'])->with('globalgradingsetvalues')->first();
	        if($globalGradingSetData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No data found with provided global grading sets id";
	        }else{
	        	$result['data']=$globalGradingSetData;
	        	$returnArr['status']=2;
                $returnArr['content']=$result;
                $returnArr['message']="Global grading sets information fetched successfully";
	        }
	    }
        catch(\Exception $e){
        	$returnArr['status']=6;
	        $returnArr['content']=$e;
	        $returnArr['message']="Something went wrong";
        }

        return $returnArr; 
	}

	/**
    * saving global grading set Method
    * Saving a global grading set and activating that set
    * Return : saved global grading set informations
    **/
    public function saveGlobalGradingSet(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		// $input=$request->all();
		$input=array_map('trim', $request->all());
		try{
			// ini_set("post_max_size", "32M");
			// ini_set("upload_max_filesize", "32M");
			// ini_set("memory_limit", "20000M");
			DB::beginTransaction();
			$guard=CustomHelper::getGuard();
			$currentUser = \Auth::guard($guard)->user();
    		$logedInUser=isset($currentUser->id) ? $currentUser->id : NULL;

    		$validationRules=[
	            'global_grading_sets_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, global grading set id is not provided";
	            return $returnArr;
	        }

	        $globalGradingSetData=$this->globalGradingSets->where('global_grading_sets_id', $input['global_grading_sets_id'])->with('globalgradingsetvalues')->first();
	        if($globalGradingSetData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No data found with provided global grading sets id";
	        }else{
	        	if(!empty($globalGradingSetData['globalgradingsetvalues'])){
	        		foreach ($globalGradingSetData['globalgradingsetvalues'] as $setGradeKey => $setGradeVal) {
	        			$setGradeId = $setGradeVal['global_grading_set_values_id'];
	        			$updData['format_grade']=(isset($input['format_grade_'.$setGradeId]) and $input['format_grade_'.$setGradeId] != "") ?  $input['format_grade_'.$setGradeId] : NULL;
	        			$updData['ip_address']=$request->ip();
					    $updData['stand']=\Carbon\Carbon::now()->toDateTimeString();
					    $updData['bearbeiter_id']=$logedInUser;
					    $updateResult = $this->globalGradingSetValues->where('global_grading_set_values_id', $setGradeId)->update($updData);
	        		}
	        		$updSetData['active'] = 0;
	        		$updateGradeSet = $this->globalGradingSets->where('global_grading_sets_id', '!=', $input['global_grading_sets_id'])->update($updSetData);

	        		$updSetFinalData['active'] = 1;
	        		$updSetFinalData['ip_address']=$request->ip();
				    $updSetFinalData['stand']=\Carbon\Carbon::now()->toDateTimeString();
				    $updSetFinalData['bearbeiter_id']=$logedInUser;
	        		$updateGradeSetFinal = $this->globalGradingSets->where('global_grading_sets_id', $input['global_grading_sets_id'])->update($updSetFinalData);
	        		if($updateGradeSetFinal){
						$savedData=$this->globalGradingSets->where('global_grading_sets_id', $input['global_grading_sets_id'])->with('globalgradingsetvalues')->first();
						$result['data']=$savedData;
				    	$returnArr['status']=2;
		                $returnArr['content']=$result;
		                $returnArr['message']="Setting saved successfully";
					} else {
			    		$returnArr['status']=5;
		                $returnArr['content']="";
		                $returnArr['message']="Operation failed";
			    	}
	        	}else{
	        		$returnArr['status']=4;
			        $returnArr['content']="";
			        $returnArr['message']="No data found";
	        	}
	        }
		    
		    DB::commit();
		    
	    }
        catch(\Exception $e){
        	DB::rollback();
        	$returnArr['status']=6;
	        $returnArr['content']=$e;
	        $returnArr['message']="Something went wrong";
        }

        return $returnArr; 
	}

}
