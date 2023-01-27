<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\FilesystemServiceProvider;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use App\Libraries\Helpers as CustomHelper;
use Image;

class QuelleController extends Controller
{

    public function __construct(\App\User $user, \App\Admin $admin, \App\Quelle $quelle, \App\QuelleGradingSettings $quelleGradingSettings, \App\QuelleSymptomSettings $quelleSymptomSettings, \App\GlobalGradingSettings $globalGradingSettings){
        $this->user = $user;
        $this->admin = $admin;
        $this->quelle = $quelle;
        $this->quelleGradingSettings = $quelleGradingSettings;
        $this->quelleSymptomSettings = $quelleSymptomSettings;
        $this->globalGradingSettings = $globalGradingSettings;
        $this->dateFormat=config('constants.date_format');
        $this->dateTimeFormat=config('constants.date_time_format');
    }

    /**
    * Get Global Gradings Settings Method
    * Get Global Gradings Settings information
    * Return : a Global Grading Settings informations
    **/
    public function globalGradingSettings(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		//$input=$request->all();
		$input=array_map('trim', $request->all());
		try{
		    $globalGradingData=$this->globalGradingSettings->where('global_grading_settings_id', 1)->first();
	        if($globalGradingData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="Global grading settings is not available.";
	        }else{
	        	$result['data']=$globalGradingData;
	        	$returnArr['status']=2;
                $returnArr['content']=$result;
                $returnArr['message']="Global grading settings information fetched successfully";
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
    * saving Quelle grading Method
    * Saving a Quelle's gradings
    * Return : saved Quelle garding informations
    **/
    public function saveQuelleSettings(Request $request)
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
	            'quelle_settings_quelle_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, quelle_id not provided";
	            return $returnArr;
	        }

	        // Insert data Array start
        	$insertData['quelle_id']= (isset($input['quelle_settings_quelle_id']) and $input['quelle_settings_quelle_id'] != "") ? $input['quelle_settings_quelle_id'] : NULL;
		    $insertData['normal']= (isset($input['normal']) and $input['normal'] != "")? $input['normal'] : NULL;
		    $insertData['normal_within_parentheses']= (isset($input['normal_within_parentheses']) and $input['normal_within_parentheses'] != "") ? $input['normal_within_parentheses'] : NULL;
		    $insertData['normal_end_with_t']= (isset($input['normal_end_with_t']) and $input['normal_end_with_t'] != "") ? $input['normal_end_with_t'] : NULL;
		    $insertData['normal_end_with_tt']= (isset($input['normal_end_with_tt']) and $input['normal_end_with_tt'] != "")? $input['normal_end_with_tt'] : NULL;
		    $insertData['normal_begin_with_degree']= (isset($input['normal_begin_with_degree']) and $input['normal_begin_with_degree'] != "")? $input['normal_begin_with_degree'] : NULL;
		    $insertData['normal_end_with_degree']= (isset($input['normal_end_with_degree']) and $input['normal_end_with_degree'] != "")? $input['normal_end_with_degree'] : NULL;
		    $insertData['normal_begin_with_asterisk']= (isset($input['normal_begin_with_asterisk']) and $input['normal_begin_with_asterisk'] != "")? $input['normal_begin_with_asterisk'] : NULL;
		    $insertData['normal_begin_with_asterisk_end_with_t']= (isset($input['normal_begin_with_asterisk_end_with_t']) and $input['normal_begin_with_asterisk_end_with_t'] != "") ? $input['normal_begin_with_asterisk_end_with_t'] : NULL; 
		    $insertData['normal_begin_with_asterisk_end_with_tt']= (isset($input['normal_begin_with_asterisk_end_with_tt']) and $input['normal_begin_with_asterisk_end_with_tt'] !="" ) ? $input['normal_begin_with_asterisk_end_with_tt'] : NULL;
		    $insertData['normal_begin_with_asterisk_end_with_degree']= (isset($input['normal_begin_with_asterisk_end_with_degree']) and $input['normal_begin_with_asterisk_end_with_degree'] !="" ) ? $input['normal_begin_with_asterisk_end_with_degree'] : NULL;
		    $insertData['sperrschrift']= (isset($input['sperrschrift']) and $input['sperrschrift'] !="" ) ? $input['sperrschrift'] : NULL;
		    $insertData['sperrschrift_begin_with_degree']= (isset($input['sperrschrift_begin_with_degree']) and $input['sperrschrift_begin_with_degree'] !="" ) ? $input['sperrschrift_begin_with_degree'] : NULL;
		    $insertData['sperrschrift_begin_with_asterisk']= (isset($input['sperrschrift_begin_with_asterisk']) and $input['sperrschrift_begin_with_asterisk'] !="" ) ? $input['sperrschrift_begin_with_asterisk'] : NULL;
		    $insertData['sperrschrift_bold']= (isset($input['sperrschrift_bold']) and $input['sperrschrift_bold'] !="" ) ? $input['sperrschrift_bold'] : NULL;
		    $insertData['sperrschrift_bold_begin_with_degree']= (isset($input['sperrschrift_bold_begin_with_degree']) and $input['sperrschrift_bold_begin_with_degree'] !="" ) ? $input['sperrschrift_bold_begin_with_degree'] : NULL;
		    $insertData['sperrschrift_bold_begin_with_asterisk']= (isset($input['sperrschrift_bold_begin_with_asterisk']) and $input['sperrschrift_bold_begin_with_asterisk'] !="" ) ? $input['sperrschrift_bold_begin_with_asterisk'] : NULL;
		    $insertData['kursiv']= (isset($input['kursiv']) and $input['kursiv'] !="" ) ? $input['kursiv'] : NULL;
		    $insertData['kursiv_end_with_t']= (isset($input['kursiv_end_with_t']) and $input['kursiv_end_with_t'] !="" ) ? $input['kursiv_end_with_t'] : NULL;
		    $insertData['kursiv_end_with_tt']= (isset($input['kursiv_end_with_tt']) and $input['kursiv_end_with_tt'] !="" ) ? $input['kursiv_end_with_tt'] : NULL;
		    $insertData['kursiv_begin_with_degree']= (isset($input['kursiv_begin_with_degree']) and $input['kursiv_begin_with_degree'] !="" ) ? $input['kursiv_begin_with_degree'] : NULL;
		    $insertData['kursiv_end_with_degree']= (isset($input['kursiv_end_with_degree']) and $input['kursiv_end_with_degree'] !="" ) ? $input['kursiv_end_with_degree'] : NULL;
		    $insertData['kursiv_begin_with_asterisk']= (isset($input['kursiv_begin_with_asterisk']) and $input['kursiv_begin_with_asterisk'] !="" ) ? $input['kursiv_begin_with_asterisk'] : NULL;
		    $insertData['kursiv_begin_with_asterisk_end_with_t']= (isset($input['kursiv_begin_with_asterisk_end_with_t']) and $input['kursiv_begin_with_asterisk_end_with_t'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_t'] : NULL;
		    $insertData['kursiv_begin_with_asterisk_end_with_tt']= (isset($input['kursiv_begin_with_asterisk_end_with_tt']) and $input['kursiv_begin_with_asterisk_end_with_tt'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_tt'] : NULL;
		    $insertData['kursiv_begin_with_asterisk_end_with_degree']= (isset($input['kursiv_begin_with_asterisk_end_with_degree']) and $input['kursiv_begin_with_asterisk_end_with_degree'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_degree'] : NULL;
		    $insertData['kursiv_bold']= (isset($input['kursiv_bold']) and $input['kursiv_bold'] !="" ) ? $input['kursiv_bold'] : NULL;
		    $insertData['kursiv_bold_begin_with_asterisk_end_with_t']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_t']) and $input['kursiv_bold_begin_with_asterisk_end_with_t'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_t'] : NULL;
		    $insertData['kursiv_bold_begin_with_asterisk_end_with_tt']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_tt']) and $input['kursiv_bold_begin_with_asterisk_end_with_tt'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_tt'] : NULL;
		    $insertData['kursiv_bold_begin_with_degree']= (isset($input['kursiv_bold_begin_with_degree']) and $input['kursiv_bold_begin_with_degree'] !="" ) ? $input['kursiv_bold_begin_with_degree'] : NULL;
		    $insertData['kursiv_bold_begin_with_asterisk']= (isset($input['kursiv_bold_begin_with_asterisk']) and $input['kursiv_bold_begin_with_asterisk'] !="" ) ? $input['kursiv_bold_begin_with_asterisk'] : NULL;
		    $insertData['kursiv_bold_begin_with_asterisk_end_with_degree']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_degree']) and $input['kursiv_bold_begin_with_asterisk_end_with_degree'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_degree'] : NULL;
		    $insertData['fett']= (isset($input['fett']) and $input['fett'] !="" ) ? $input['fett'] : NULL;
		    $insertData['fett_end_with_t']= (isset($input['fett_end_with_t']) and $input['fett_end_with_t'] !="" ) ? $input['fett_end_with_t'] : NULL;
		    $insertData['fett_end_with_tt']= (isset($input['fett_end_with_tt']) and $input['fett_end_with_tt'] !="" ) ? $input['fett_end_with_tt'] : NULL;
		    $insertData['fett_begin_with_degree']= (isset($input['fett_begin_with_degree']) and $input['fett_begin_with_degree'] !="" ) ? $input['fett_begin_with_degree'] : NULL;
		    $insertData['fett_end_with_degree']= (isset($input['fett_end_with_degree']) and $input['fett_end_with_degree'] !="" ) ? $input['fett_end_with_degree'] : NULL;
		    $insertData['fett_begin_with_asterisk']= (isset($input['fett_begin_with_asterisk']) and $input['fett_begin_with_asterisk'] !="" ) ? $input['fett_begin_with_asterisk'] : NULL;
		    $insertData['fett_begin_with_asterisk_end_with_t']= (isset($input['fett_begin_with_asterisk_end_with_t']) and $input['fett_begin_with_asterisk_end_with_t'] !="" ) ? $input['fett_begin_with_asterisk_end_with_t'] : NULL;
		    $insertData['fett_begin_with_asterisk_end_with_tt']= (isset($input['fett_begin_with_asterisk_end_with_tt']) and $input['fett_begin_with_asterisk_end_with_tt'] !="" ) ? $input['fett_begin_with_asterisk_end_with_tt'] : NULL;
		    $insertData['fett_begin_with_asterisk_end_with_degree']= (isset($input['fett_begin_with_asterisk_end_with_degree']) and $input['fett_begin_with_asterisk_end_with_degree'] !="" ) ? $input['fett_begin_with_asterisk_end_with_degree'] : NULL;
		    $insertData['gross']= (isset($input['gross']) and $input['gross'] !="" ) ? $input['gross'] : NULL;
		    $insertData['gross_begin_with_degree']= (isset($input['gross_begin_with_degree']) and $input['gross_begin_with_degree'] !="" ) ? $input['gross_begin_with_degree'] : NULL;
		    $insertData['gross_begin_with_asterisk']= (isset($input['gross_begin_with_asterisk']) and $input['gross_begin_with_asterisk'] !="" ) ? $input['gross_begin_with_asterisk'] : NULL;
		    $insertData['gross_bold']= (isset($input['gross_bold']) and $input['gross_bold'] !="" ) ? $input['gross_bold'] : NULL;
		    $insertData['gross_bold_begin_with_degree']= (isset($input['gross_bold_begin_with_degree']) and $input['gross_bold_begin_with_degree'] !="" ) ? $input['gross_bold_begin_with_degree'] : NULL;
		    $insertData['gross_bold_begin_with_asterisk']= (isset($input['gross_bold_begin_with_asterisk']) and $input['gross_bold_begin_with_asterisk'] !="" ) ? $input['gross_bold_begin_with_asterisk'] : NULL;
		    $insertData['pi_sign']= (isset($input['pi_sign']) and $input['pi_sign'] !="" ) ? $input['pi_sign'] : NULL;
		    $insertData['one_bar']= (isset($input['one_bar']) and $input['one_bar'] !="" ) ? $input['one_bar'] : NULL;
		    $insertData['two_bar']= (isset($input['two_bar']) and $input['two_bar'] !="" ) ? $input['two_bar'] : NULL;
		    $insertData['three_bar']= (isset($input['three_bar']) and $input['three_bar'] !="" ) ? $input['three_bar'] : NULL;
		    $insertData['three_and_half_bar']= (isset($input['three_and_half_bar']) and $input['three_and_half_bar'] !="" ) ? $input['three_and_half_bar'] : NULL;
		    $insertData['four_bar']= (isset($input['four_bar']) and $input['four_bar'] !="" ) ? $input['four_bar'] : NULL;
		    $insertData['four_and_half_bar']= (isset($input['four_and_half_bar']) and $input['four_and_half_bar'] !="" ) ? $input['four_and_half_bar'] : NULL;
		    $insertData['five_bar']= (isset($input['five_bar']) and $input['five_bar'] !="" ) ? $input['five_bar'] : NULL;

		    $insertData['active']= (isset($input['active']) and $input['active'] !="" ) ? $input['active'] : 1;
		    $insertData['ip_address']=$request->ip();
		    $insertData['ersteller_datum']=\Carbon\Carbon::now()->toDateTimeString();
		    $insertData['ersteller_id']=$logedInUser;
		    // Insert data Array end 
		    
		    // Update data Array Start
		    $updateData['normal']= (isset($input['normal']) and $input['normal'] != "")? $input['normal'] : NULL;
		    $updateData['normal_within_parentheses']= (isset($input['normal_within_parentheses']) and $input['normal_within_parentheses'] != "") ? $input['normal_within_parentheses'] : NULL;
		    $updateData['normal_end_with_t']= (isset($input['normal_end_with_t']) and $input['normal_end_with_t'] != "") ? $input['normal_end_with_t'] : NULL;
		    $updateData['normal_end_with_tt']= (isset($input['normal_end_with_tt']) and $input['normal_end_with_tt'] != "")? $input['normal_end_with_tt'] : NULL;
		    $updateData['normal_begin_with_degree']= (isset($input['normal_begin_with_degree']) and $input['normal_begin_with_degree'] != "")? $input['normal_begin_with_degree'] : NULL;
		    $updateData['normal_end_with_degree']= (isset($input['normal_end_with_degree']) and $input['normal_end_with_degree'] != "")? $input['normal_end_with_degree'] : NULL;
		    $updateData['normal_begin_with_asterisk']= (isset($input['normal_begin_with_asterisk']) and $input['normal_begin_with_asterisk'] != "")? $input['normal_begin_with_asterisk'] : NULL;
		    $updateData['normal_begin_with_asterisk_end_with_t']= (isset($input['normal_begin_with_asterisk_end_with_t']) and $input['normal_begin_with_asterisk_end_with_t'] != "") ? $input['normal_begin_with_asterisk_end_with_t'] : NULL; 
		    $updateData['normal_begin_with_asterisk_end_with_tt']= (isset($input['normal_begin_with_asterisk_end_with_tt']) and $input['normal_begin_with_asterisk_end_with_tt'] !="" ) ? $input['normal_begin_with_asterisk_end_with_tt'] : NULL;
		    $updateData['normal_begin_with_asterisk_end_with_degree']= (isset($input['normal_begin_with_asterisk_end_with_degree']) and $input['normal_begin_with_asterisk_end_with_degree'] !="" ) ? $input['normal_begin_with_asterisk_end_with_degree'] : NULL;
		    $updateData['sperrschrift']= (isset($input['sperrschrift']) and $input['sperrschrift'] !="" ) ? $input['sperrschrift'] : NULL;
		    $updateData['sperrschrift_begin_with_degree']= (isset($input['sperrschrift_begin_with_degree']) and $input['sperrschrift_begin_with_degree'] !="" ) ? $input['sperrschrift_begin_with_degree'] : NULL;
		    $updateData['sperrschrift_begin_with_asterisk']= (isset($input['sperrschrift_begin_with_asterisk']) and $input['sperrschrift_begin_with_asterisk'] !="" ) ? $input['sperrschrift_begin_with_asterisk'] : NULL;
		    $updateData['sperrschrift_bold']= (isset($input['sperrschrift_bold']) and $input['sperrschrift_bold'] !="" ) ? $input['sperrschrift_bold'] : NULL;
		    $updateData['sperrschrift_bold_begin_with_degree']= (isset($input['sperrschrift_bold_begin_with_degree']) and $input['sperrschrift_bold_begin_with_degree'] !="" ) ? $input['sperrschrift_bold_begin_with_degree'] : NULL;
		    $updateData['sperrschrift_bold_begin_with_asterisk']= (isset($input['sperrschrift_bold_begin_with_asterisk']) and $input['sperrschrift_bold_begin_with_asterisk'] !="" ) ? $input['sperrschrift_bold_begin_with_asterisk'] : NULL;
		    $updateData['kursiv']= (isset($input['kursiv']) and $input['kursiv'] !="" ) ? $input['kursiv'] : NULL;
		    $updateData['kursiv_end_with_t']= (isset($input['kursiv_end_with_t']) and $input['kursiv_end_with_t'] !="" ) ? $input['kursiv_end_with_t'] : NULL;
		    $updateData['kursiv_end_with_tt']= (isset($input['kursiv_end_with_tt']) and $input['kursiv_end_with_tt'] !="" ) ? $input['kursiv_end_with_tt'] : NULL;
		    $updateData['kursiv_begin_with_degree']= (isset($input['kursiv_begin_with_degree']) and $input['kursiv_begin_with_degree'] !="" ) ? $input['kursiv_begin_with_degree'] : NULL;
		    $updateData['kursiv_end_with_degree']= (isset($input['kursiv_end_with_degree']) and $input['kursiv_end_with_degree'] !="" ) ? $input['kursiv_end_with_degree'] : NULL;
		    $updateData['kursiv_begin_with_asterisk']= (isset($input['kursiv_begin_with_asterisk']) and $input['kursiv_begin_with_asterisk'] !="" ) ? $input['kursiv_begin_with_asterisk'] : NULL;
		    $updateData['kursiv_begin_with_asterisk_end_with_t']= (isset($input['kursiv_begin_with_asterisk_end_with_t']) and $input['kursiv_begin_with_asterisk_end_with_t'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_t'] : NULL;
		    $updateData['kursiv_begin_with_asterisk_end_with_tt']= (isset($input['kursiv_begin_with_asterisk_end_with_tt']) and $input['kursiv_begin_with_asterisk_end_with_tt'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_tt'] : NULL;
		    $updateData['kursiv_begin_with_asterisk_end_with_degree']= (isset($input['kursiv_begin_with_asterisk_end_with_degree']) and $input['kursiv_begin_with_asterisk_end_with_degree'] !="" ) ? $input['kursiv_begin_with_asterisk_end_with_degree'] : NULL;
		    $updateData['kursiv_bold']= (isset($input['kursiv_bold']) and $input['kursiv_bold'] !="" ) ? $input['kursiv_bold'] : NULL;
		    $updateData['kursiv_bold_begin_with_asterisk_end_with_t']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_t']) and $input['kursiv_bold_begin_with_asterisk_end_with_t'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_t'] : NULL;
		    $updateData['kursiv_bold_begin_with_asterisk_end_with_tt']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_tt']) and $input['kursiv_bold_begin_with_asterisk_end_with_tt'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_tt'] : NULL;
		    $updateData['kursiv_bold_begin_with_degree']= (isset($input['kursiv_bold_begin_with_degree']) and $input['kursiv_bold_begin_with_degree'] !="" ) ? $input['kursiv_bold_begin_with_degree'] : NULL;
		    $updateData['kursiv_bold_begin_with_asterisk']= (isset($input['kursiv_bold_begin_with_asterisk']) and $input['kursiv_bold_begin_with_asterisk'] !="" ) ? $input['kursiv_bold_begin_with_asterisk'] : NULL;
		    $updateData['kursiv_bold_begin_with_asterisk_end_with_degree']= (isset($input['kursiv_bold_begin_with_asterisk_end_with_degree']) and $input['kursiv_bold_begin_with_asterisk_end_with_degree'] !="" ) ? $input['kursiv_bold_begin_with_asterisk_end_with_degree'] : NULL;
		    $updateData['fett']= (isset($input['fett']) and $input['fett'] !="" ) ? $input['fett'] : NULL;
		    $updateData['fett_end_with_t']= (isset($input['fett_end_with_t']) and $input['fett_end_with_t'] !="" ) ? $input['fett_end_with_t'] : NULL;
		    $updateData['fett_end_with_tt']= (isset($input['fett_end_with_tt']) and $input['fett_end_with_tt'] !="" ) ? $input['fett_end_with_tt'] : NULL;
		    $updateData['fett_begin_with_degree']= (isset($input['fett_begin_with_degree']) and $input['fett_begin_with_degree'] !="" ) ? $input['fett_begin_with_degree'] : NULL;
		    $updateData['fett_end_with_degree']= (isset($input['fett_end_with_degree']) and $input['fett_end_with_degree'] !="" ) ? $input['fett_end_with_degree'] : NULL;
		    $updateData['fett_begin_with_asterisk']= (isset($input['fett_begin_with_asterisk']) and $input['fett_begin_with_asterisk'] !="" ) ? $input['fett_begin_with_asterisk'] : NULL;
		    $updateData['fett_begin_with_asterisk_end_with_t']= (isset($input['fett_begin_with_asterisk_end_with_t']) and $input['fett_begin_with_asterisk_end_with_t'] !="" ) ? $input['fett_begin_with_asterisk_end_with_t'] : NULL;
		    $updateData['fett_begin_with_asterisk_end_with_tt']= (isset($input['fett_begin_with_asterisk_end_with_tt']) and $input['fett_begin_with_asterisk_end_with_tt'] !="" ) ? $input['fett_begin_with_asterisk_end_with_tt'] : NULL;
		    $updateData['fett_begin_with_asterisk_end_with_degree']= (isset($input['fett_begin_with_asterisk_end_with_degree']) and $input['fett_begin_with_asterisk_end_with_degree'] !="" ) ? $input['fett_begin_with_asterisk_end_with_degree'] : NULL;
		    $updateData['gross']= (isset($input['gross']) and $input['gross'] !="" ) ? $input['gross'] : NULL;
		    $updateData['gross_begin_with_degree']= (isset($input['gross_begin_with_degree']) and $input['gross_begin_with_degree'] !="" ) ? $input['gross_begin_with_degree'] : NULL;
		    $updateData['gross_begin_with_asterisk']= (isset($input['gross_begin_with_asterisk']) and $input['gross_begin_with_asterisk'] !="" ) ? $input['gross_begin_with_asterisk'] : NULL;
		    $updateData['gross_bold']= (isset($input['gross_bold']) and $input['gross_bold'] !="" ) ? $input['gross_bold'] : NULL;
		    $updateData['gross_bold_begin_with_degree']= (isset($input['gross_bold_begin_with_degree']) and $input['gross_bold_begin_with_degree'] !="" ) ? $input['gross_bold_begin_with_degree'] : NULL;
		    $updateData['gross_bold_begin_with_asterisk']= (isset($input['gross_bold_begin_with_asterisk']) and $input['gross_bold_begin_with_asterisk'] !="" ) ? $input['gross_bold_begin_with_asterisk'] : NULL;
		    $updateData['pi_sign']= (isset($input['pi_sign']) and $input['pi_sign'] !="" ) ? $input['pi_sign'] : NULL;
		    $updateData['one_bar']= (isset($input['one_bar']) and $input['one_bar'] !="" ) ? $input['one_bar'] : NULL;
		    $updateData['two_bar']= (isset($input['two_bar']) and $input['two_bar'] !="" ) ? $input['two_bar'] : NULL;
		    $updateData['three_bar']= (isset($input['three_bar']) and $input['three_bar'] !="" ) ? $input['three_bar'] : NULL;
		    $updateData['three_and_half_bar']= (isset($input['three_and_half_bar']) and $input['three_and_half_bar'] !="" ) ? $input['three_and_half_bar'] : NULL;
		    $updateData['four_bar']= (isset($input['four_bar']) and $input['four_bar'] !="" ) ? $input['four_bar'] : NULL;
		    $updateData['four_and_half_bar']= (isset($input['four_and_half_bar']) and $input['four_and_half_bar'] !="" ) ? $input['four_and_half_bar'] : NULL;
		    $updateData['five_bar']= (isset($input['five_bar']) and $input['five_bar'] !="" ) ? $input['five_bar'] : NULL;
        	$updateData['ip_address']=$request->ip();
		    $updateData['stand']=\Carbon\Carbon::now()->toDateTimeString();
		    $updateData['bearbeiter_id']=$logedInUser;
		    // Update data Array End


		    // Symptom type settings insert array start
        	$symptomTypeInsertData['quelle_id']= (isset($input['quelle_settings_quelle_id']) and $input['quelle_settings_quelle_id'] != "") ? $input['quelle_settings_quelle_id'] : NULL;
        	$symptomTypeInsertData['symptom_type_for_whole']= (isset($input['symptom_type_for_whole']) and $input['symptom_type_for_whole'] !="" ) ? $input['symptom_type_for_whole'] : NULL;
        	$symptomTypeInsertData['symptoms_with_reference']= (isset($input['symptoms_with_reference']) and $input['symptoms_with_reference'] !="" ) ? $input['symptoms_with_reference'] : NULL;
        	$symptomTypeInsertData['symptoms_without_reference']= (isset($input['symptoms_without_reference']) and $input['symptoms_without_reference'] !="" ) ? $input['symptoms_without_reference'] : NULL;
        	$symptomTypeInsertData['symptoms_with_provers']= (isset($input['symptoms_with_provers']) and $input['symptoms_with_provers'] !="" ) ? $input['symptoms_with_provers'] : NULL;
        	$symptomTypeInsertData['symptoms_without_provers']= (isset($input['symptoms_without_provers']) and $input['symptoms_without_provers'] !="" ) ? $input['symptoms_without_provers'] : NULL;
        	$symptomTypeInsertData['symptom_with_A_f_d_H']= (isset($input['symptom_with_A_f_d_H']) and $input['symptom_with_A_f_d_H'] !="" ) ? $input['symptom_with_A_f_d_H'] : NULL;
        	$symptomTypeInsertData['active']= (isset($input['active']) and $input['active'] !="" ) ? $input['active'] : 1;
		    $symptomTypeInsertData['ip_address']=$request->ip();
		    $symptomTypeInsertData['ersteller_datum']=\Carbon\Carbon::now()->toDateTimeString();
		    $symptomTypeInsertData['ersteller_id']=$logedInUser;
		    // Symptom type settings insert array end
		    // Symptom type settings update array start
		    $symptomTypeUpdateData['symptom_type_for_whole']= (isset($input['symptom_type_for_whole']) and $input['symptom_type_for_whole'] !="" ) ? $input['symptom_type_for_whole'] : NULL;
        	$symptomTypeUpdateData['symptoms_with_reference']= (isset($input['symptoms_with_reference']) and $input['symptoms_with_reference'] !="" ) ? $input['symptoms_with_reference'] : NULL;
        	$symptomTypeUpdateData['symptoms_without_reference']= (isset($input['symptoms_without_reference']) and $input['symptoms_without_reference'] !="" ) ? $input['symptoms_without_reference'] : NULL;
        	$symptomTypeUpdateData['symptoms_with_provers']= (isset($input['symptoms_with_provers']) and $input['symptoms_with_provers'] !="" ) ? $input['symptoms_with_provers'] : NULL;
        	$symptomTypeUpdateData['symptoms_without_provers']= (isset($input['symptoms_without_provers']) and $input['symptoms_without_provers'] !="" ) ? $input['symptoms_without_provers'] : NULL;
		    $symptomTypeUpdateData['symptom_with_A_f_d_H']= (isset($input['symptom_with_A_f_d_H']) and $input['symptom_with_A_f_d_H'] !="" ) ? $input['symptom_with_A_f_d_H'] : NULL;
        	$symptomTypeUpdateData['ip_address']=$request->ip();
		    $symptomTypeUpdateData['stand']=\Carbon\Carbon::now()->toDateTimeString();
		    $symptomTypeUpdateData['bearbeiter_id']=$logedInUser;
		    // Symptom type settings update array end

		    // Grading Setting 
		    $quelleGradingData=$this->quelleGradingSettings->where('quelle_id', $insertData['quelle_id'])->first();
	        if($quelleGradingData === null){
	        	$quelleResult=$this->quelleGradingSettings->create($insertData);
	        } else {
	        	$quelleResult=$this->quelleGradingSettings->where('quelle_id', $insertData['quelle_id'])->update($updateData);
	        }

	        // Symptom Type settings
	       	$quelleSymptomTypeData=$this->quelleSymptomSettings->where('quelle_id', $symptomTypeInsertData['quelle_id'])->first();
	       	if($quelleSymptomTypeData === null){
	       		$quelleResult=$this->quelleSymptomSettings->create($symptomTypeInsertData);
	       	} else {
	       		$quelleResult=$this->quelleSymptomSettings->where('quelle_id', $symptomTypeInsertData['quelle_id'])->update($symptomTypeUpdateData);
	       	}

	       	if($quelleResult){
        		$savedQuelleDetailedData=$this->quelle->where('quelle_id', $insertData['quelle_id'])->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings', 'quellesymptomsettings')->first();
				$result['data']=$savedQuelleDetailedData;
		    	$returnArr['status']=2;
                $returnArr['content']=$result;
                $returnArr['message']="Quelle gradings saved successfully";
        	} else {
	    		$returnArr['status']=5;
                $returnArr['content']="";
                $returnArr['message']="Operation failed";
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

    /**
    * Get Quelle Gradings Method
    * Get Quelle Gradings information by it's ID
    * Return : a Quelle's grading informations
    **/
    public function quelleSettings(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		//$input=$request->all();
		$input=array_map('trim', $request->all());
		try{
			$validationRules=[
	            'quelle_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, quelle_id not provided";
	            return $returnArr;
	        }

	        $result['data']['grading_settings']= array();
	        $result['data']['symptom_type_settings']= array();
		    $quelleGradingData=$this->quelleGradingSettings->where('quelle_id', $input['quelle_id'])->first();
	        if($quelleGradingData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No Quelle grading settings available for this quelle. You can set now!";
	        }else{
	        	$quelleSymptomSettingData=$this->quelleSymptomSettings->where('quelle_id', $input['quelle_id'])->first();
	        	$result['data']['grading_settings']=$quelleGradingData;
	        	$result['data']['symptom_type_settings']= ($quelleSymptomSettingData === null) ? array() : $quelleSymptomSettingData;
	        	$returnArr['status']=2;
                $returnArr['content']=$result;
                $returnArr['message']="Quelle gradings information fetched successfully";
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
    * Fetching all Quelle and Zeitschrift Method 
    * Return : all Quelle and Zeitschrift 
    **/
    public function allQuelleZeitschrift(Request $request){
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
	        	$quelleData=$this->quelle
	        				->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')
	        				->where('quelle.quelle_type_id', 1)
	        				->orWhere('quelle.quelle_type_id', 2)
	    					->orderBy('quelle.ersteller_datum', 'desc')
	    					->get();
	    		$dataArray['data']=$quelleData->toArray();
	        }else{
	        	$quelleData=$this->quelle
	        				->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')
	        				->where('quelle.quelle_type_id', 1)
	        				->orWhere('quelle.quelle_type_id', 2)
	    					->orderBy('quelle.ersteller_datum', 'desc')
	    					->paginate($dataPerPage);
	    		$dataArray=$quelleData->toArray();
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
    * Fetching all Quelle Method 
    * Return : all Quelle 
    **/
    public function allQuelle(Request $request){
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
	        	$quelleData=$this->quelle
	        				->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')
	        				->where('quelle.quelle_type_id', 1)
	    					->orderBy('quelle.ersteller_datum', 'desc')
	    					->get();
	    		$dataArray['data']=$quelleData->toArray();
	        }else{
	        	$quelleData=$this->quelle
	        				->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')
	        				->where('quelle.quelle_type_id', 1)
	    					->orderBy('quelle.ersteller_datum', 'desc')
	    					->paginate($dataPerPage);
	    		$dataArray=$quelleData->toArray();
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
    * adding Quelle Method
    * Adding a Quelle
    * Return : added Quelle informations
    **/
    public function addQuelle(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		$input=$request->all();
		//$input=array_map('trim', $request->all());

		try{
			// ini_set("post_max_size", "32M");
			// ini_set("upload_max_filesize", "32M");
			// ini_set("memory_limit", "20000M");
			DB::beginTransaction();
			$guard=CustomHelper::getGuard();
			$currentUser = \Auth::guard($guard)->user();
    		$logedInUser=isset($currentUser->id) ? $currentUser->id : NULL;
    		$isReadyToInsert=0;

			$validationRules=[
	            'code' => 'required',
	            'titel' => 'required',
	            'sprache' => 'required',
	            // 'jahr' => 'required|digits:4'
	            'jahr' => 'required'
	            // 'auflage' => 'required'
	            // 'verlag_id' => 'required'
	        ];
	        if(isset($input['file_url']) && $input['file_url']!=""){
	        	$fileValidationRule=[
		            'file_url' => 'required|mimes:pdf,doc,docx|max:31000'
		        ];
		        $validationRules=array_merge($validationRules, $fileValidationRule);
	        }
	        
	        // return $validationRules;
	        // exit();
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed";
	            return $returnArr;
	        }

	        if(isset($input['file_url']) && $input['file_url']!=""){
	        	$micoTime=preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime());
		        $fileName = $micoTime.'.'.$request->file_url->getClientOriginalExtension();

		        $tempUploadPath = storage_path('temp-files/');
		        $uploadPath = storage_path('uploads/quelle/');
		        $file = $request->file('file_url');
		        $uploadRes=$file->move($tempUploadPath, $fileName);
		        if($uploadRes){
		        	if(rename($tempUploadPath."/".$fileName, $uploadPath."/".$fileName))
		        		$isReadyToInsert=1;
		        }
	        }
	        else
	        	$isReadyToInsert=1;
	        
	        if($isReadyToInsert == 1){
	        	$insertData['quelle_type_id']= 1;
	        	$insertData['quelle_schema_id']= (isset($input['quelle_schema_id']) and $input['quelle_schema_id'] != "") ? $input['quelle_schema_id'] : NULL;
			    $insertData['herkunft_id']= (isset($input['herkunft_id']) and $input['herkunft_id'] != "")? $input['herkunft_id'] : NULL;
			    $insertData['code']= (isset($input['code']) and $input['code'] != "") ? $input['code'] : NULL;
			    $insertData['titel']= (isset($input['titel']) and $input['titel'] != "") ? $input['titel'] : NULL;
			    $insertData['jahr']= (isset($input['jahr']) and $input['jahr'] != "")? $input['jahr'] : NULL;
			    $insertData['band']= (isset($input['band']) and $input['band'] != "")? $input['band'] : NULL;
			    $insertData['nummer']= (isset($input['nummer']) and $input['nummer'] != "")? $input['nummer'] : NULL;
			    $insertData['auflage']= (isset($input['auflage']) and $input['auflage'] != "")? $input['auflage'] : NULL;
			    $insertData['file_url']= (isset($fileName) and $fileName !="") ? $fileName : NULL;
			    $insertData['verlag_id']= (isset($input['verlag_id']) and $input['verlag_id'] != "") ? $input['verlag_id'] : NULL; 
			    $insertData['sprache']= (isset($input['sprache']) and $input['sprache'] !="" ) ? $input['sprache'] : NULL;
			    $insertData['source_type']= (isset($input['source_type']) and $input['source_type'] !="" ) ? $input['source_type'] : NULL;
			    $insertData['autor_or_herausgeber']= (isset($input['autor_or_herausgeber']) and $input['autor_or_herausgeber'] !="" ) ? $input['autor_or_herausgeber'] : NULL;
			    $insertData['kommentar']= (isset($input['kommentar']) and $input['kommentar'] !="" ) ? $input['kommentar'] : NULL;
			    $insertData['is_coding_with_symptom_number']= (isset($input['is_coding_with_symptom_number']) and $input['is_coding_with_symptom_number'] !="" ) ? $input['is_coding_with_symptom_number'] : 1;
			    $insertData['active']= (isset($input['active']) and $input['active'] !="" ) ? $input['active'] : 1;
			    $insertData['ip_address']=$request->ip();
			    $insertData['ersteller_datum']=\Carbon\Carbon::now()->toDateTimeString();
			    $insertData['ersteller_id']=$logedInUser;

			    $quelleResult=$this->quelle->create($insertData);
			    if($quelleResult){
			    	$canProceed = 1;
			    	if(isset($input['autor_id']) and !empty($input['autor_id'])){
			    		$autorData = [];
						foreach ($input['autor_id'] as $autorKey => $autorVal) {
						    $autorData[] = [
						        'quelle_id'  => $quelleResult->id,
						        'autor_id'  	=> $autorVal,
						        'ersteller_datum' => \Carbon\Carbon::now()->toDateTimeString(),
						        'ersteller_id' => $logedInUser,
						    ];
						}

						$autorInsertRes=DB::table('quelle_autor')->insert($autorData);
				    	if($autorInsertRes == true)
				    		$canProceed = 1;
				    	else
				    		$canProceed = 0;
			    	}

			    	if(isset($input['pruefer_id']) and !empty($input['pruefer_id']) AND $canProceed == 1){
			    		$prufData = [];
						foreach ($input['pruefer_id'] as $prufKey => $prufVal) {
						    $prufData[] = [
						        'quelle_id'  => $quelleResult->id,
						        'pruefer_id'  	=> $prufVal,
						        'ersteller_datum' => \Carbon\Carbon::now()->toDateTimeString(),
						        'ersteller_id' => $logedInUser,
						    ];
						}

						$prufInsertRes=DB::table('quelle_pruefer')->insert($prufData);
				    	if($prufInsertRes == true)
				    		$canProceed = 1;
				    	else
				    		$canProceed = 0;
			    	}

			    	if($canProceed == 1){
			    		$insertedData=$this->quelle->where('quelle_id', $quelleResult->id)->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')->first();
    					$result['data']=$insertedData;
				    	$returnArr['status']=2;
		                $returnArr['content']=$result;
		                $returnArr['message']="Quelle created successfully";
			    	}
			    	else{
			    		$returnArr['status']=5;
		                $returnArr['content']="";
		                $returnArr['message']="Operation failed, could not assign the autors or pruefer";
			    	}
			    	
			    }
			    else{
			    	$returnArr['status']=5;
	                $returnArr['content']="";
	                $returnArr['message']="Operation failed, could not create the quelle";
			    }
			    DB::commit();
	        }
		    else{
		    	$returnArr['status']=5;
                $returnArr['content']="";
                $returnArr['message']="Operation failed, could not upload the file";
		    }
	    }
        catch(\Exception $e){
        	DB::rollback();
        	$returnArr['status']=6;
	        $returnArr['content']=$e;
	        $returnArr['message']="Something went wrong";
        }

        return $returnArr; 
	}

	/**
    * view Quelle Method
    * view Quelle information by it's ID
    * Return : a Quelle's informations
    **/
    public function viewQuelle(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		//$input=$request->all();
		$input=array_map('trim', $request->all());
		try{
			$validationRules=[
	            'quelle_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, quelle_id not provided";
	            return $returnArr;
	        }

		    $quelleData=$this->quelle->where('quelle_id', $input['quelle_id'])->where('quelle_type_id', 1)->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')->first();
	        if($quelleData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No quelle found with provided quelle_id";
	        }else{
	        	$result['data']=$quelleData;
	        	$returnArr['status']=2;
                $returnArr['content']=$result;
                $returnArr['message']="Quelle information fetched successfully";
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
    * User update Quelle Method
    * update Quelle information by it's ID
    * Return : updated Quelle's informations
    **/
    public function updateQuelle(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		$input=$request->all();
		//$input=array_map('trim', $request->all());
		try{
			DB::beginTransaction();
			$guard=CustomHelper::getGuard();
			$currentUser = \Auth::guard($guard)->user();
    		$logedInUser=isset($currentUser->id) ? $currentUser->id : NULL;
    		$isReadyToInsert=0;

			$validationRules=[
				'quelle_id' => 'required',
	            'code' => 'required',
	            'titel' => 'required',
	            'sprache' => 'required',
	            // 'jahr' => 'required|digits:4'
	            'jahr' => 'required'
	            // 'auflage' => 'required'
	            // 'verlag_id' => 'required'
	        ];
	        if(isset($input['file_url']) && $input['file_url']!=""){
	        	$fileValidationRule=[
		            'file_url' => 'required|mimes:pdf,doc,docx|max:31000'
		        ];
		        $validationRules=array_merge($validationRules, $fileValidationRule);
	        }

	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed";
	            return $returnArr;
	        }

	        $quelleData=$this->quelle->where('quelle_id', $input['quelle_id'])->where('quelle_type_id', 1)->first();
	        if($quelleData === null){
	        	$returnArr['status']=4;
		        $returnArr['content']="";
		        $returnArr['message']="No quelle found with provided quelle_id";
	        }
	        else{

	        	if(isset($input['file_url']) && $input['file_url']!=""){
		        	$micoTime=preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime());
			        $fileName = $micoTime.'.'.$request->file_url->getClientOriginalExtension();

			        $tempUploadPath = storage_path('temp-files/');
			        $uploadPath = storage_path('uploads/quelle/');
			        $file = $request->file('file_url');
			        $uploadRes=$file->move($tempUploadPath, $fileName);
			        if($uploadRes){
			        	if(rename($tempUploadPath."/".$fileName, $uploadPath."/".$fileName))
			        		$isReadyToInsert=1;
			        }
		        }
		        else
		        	$isReadyToInsert=1;

		        if($isReadyToInsert == 1){
		        	$updateData['quelle_schema_id']= (isset($input['quelle_schema_id']) and $input['quelle_schema_id'] != "") ? $input['quelle_schema_id'] : NULL;
				    $updateData['herkunft_id']= (isset($input['herkunft_id']) and $input['herkunft_id'] != "")? $input['herkunft_id'] : NULL;
				    $updateData['code']= (isset($input['code']) and $input['code'] != "") ? $input['code'] : NULL;
				    $updateData['titel']= (isset($input['titel']) and $input['titel'] != "") ? $input['titel'] : NULL;
				    $updateData['jahr']= (isset($input['jahr']) and $input['jahr'] != "")? $input['jahr'] : NULL;
				    $updateData['band']= (isset($input['band']) and $input['band'] != "")? $input['band'] : NULL;
				    $updateData['nummer']= (isset($input['nummer']) and $input['nummer'] != "")? $input['nummer'] : NULL;
				    $updateData['auflage']= (isset($input['auflage']) and $input['auflage'] != "")? $input['auflage'] : NULL;
				    if(isset($input['file_url']) && $input['file_url']!="")
				    	$updateData['file_url']= (isset($fileName) and $fileName !="") ? $fileName : NULL;
				    $updateData['verlag_id']= (isset($input['verlag_id']) and $input['verlag_id'] != "") ? $input['verlag_id'] : NULL; 
				    $updateData['sprache']= (isset($input['sprache']) and $input['sprache'] !="" ) ? $input['sprache'] : NULL;
				    $updateData['source_type']= (isset($input['source_type']) and $input['source_type'] !="" ) ? $input['source_type'] : NULL;
				    $updateData['autor_or_herausgeber']= (isset($input['autor_or_herausgeber']) and $input['autor_or_herausgeber'] !="" ) ? $input['autor_or_herausgeber'] : NULL;
				    $updateData['kommentar']= (isset($input['kommentar']) and $input['kommentar'] !="" ) ? $input['kommentar'] : NULL;
				    $updateData['is_coding_with_symptom_number']= (isset($input['is_coding_with_symptom_number']) and $input['is_coding_with_symptom_number'] !="" ) ? $input['is_coding_with_symptom_number'] : 1;
				    $updateData['active']= (isset($input['active']) and $input['active'] !="" ) ? $input['active'] : 1;
				    $updateData['ip_address']=$request->ip();
				    $updateData['stand']=\Carbon\Carbon::now()->toDateTimeString();
				    $updateData['bearbeiter_id']=$logedInUser;

				    $updateResult=$this->quelle->where('quelle_id', $input['quelle_id'])->update($updateData);
				    if($updateResult){
				    	$deleteExistingAutor=DB::table('quelle_autor')->where('quelle_id', $input['quelle_id'])->delete();
				    	$deleteExistingPruefer=DB::table('quelle_pruefer')->where('quelle_id', $input['quelle_id'])->delete();
				    	$canProceed = 1;

			    		if(isset($input['autor_id']) and !empty($input['autor_id'])){
				    		$autorData = [];
							foreach ($input['autor_id'] as $autorKey => $autorVal) {
							    $autorData[] = [
							        'quelle_id'  => $input['quelle_id'],
							        'autor_id'  	=> $autorVal,
							        'ersteller_datum' => \Carbon\Carbon::now()->toDateTimeString(),
							        'ersteller_id' => $logedInUser,
							    ];
							}

							$autorInsertRes=DB::table('quelle_autor')->insert($autorData);
							if($autorInsertRes == true)
								$canProceed = 1;
							else
								$canProceed = 0;
						}

						if(isset($input['pruefer_id']) and !empty($input['pruefer_id'])){
				    		$prufData = [];
							foreach ($input['pruefer_id'] as $prufKey => $prufVal) {
							    $prufData[] = [
							        'quelle_id'  => $input['quelle_id'],
							        'pruefer_id'  	=> $prufVal,
							        'ersteller_datum' => \Carbon\Carbon::now()->toDateTimeString(),
							        'ersteller_id' => $logedInUser,
							    ];
							}

							$prufInsertRes=DB::table('quelle_pruefer')->insert($prufData);
							if($prufInsertRes == true)
								$canProceed = 1;
							else
								$canProceed = 0;
						}

						if($canProceed == 1){
				    		$insertedData=$this->quelle->where('quelle_id', $input['quelle_id'])->with('pruefer', 'autoren', 'herkunft', 'verlag', 'quellegradingsettings')->first();
				    		
        					$result['data']=$insertedData;
					    	$returnArr['status']=2;
			                $returnArr['content']=$result;
			                $returnArr['message']="Quelle information updated successfully";
				    	}
				    	else{
				    		$returnArr['status']=5;
			                $returnArr['content']="";
			                $returnArr['message']="Operation failed, could not assign the autors or pruefer";
				    	}
				    		
				    }else{
				    	$returnArr['status']=5;
		                $returnArr['content']="";
		                $returnArr['message']="Operation failed, could not update the quelle";
				    }
		        }
		        else{
		        	$returnArr['status']=5;
	                $returnArr['content']="";
	                $returnArr['message']="Operation failed, could not upload the file";
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


	/**
    * User delete Quelle Method
    * delete a Quelle by it's ID
    * Return : nothing(blank)
    **/
    public function deleteQuelle(Request $request)
	{ 
		$returnArr=config('constants.return_array');
		$input=$request->all();
		//$input=array_map('trim', $request->all());
		try{
			DB::beginTransaction();
			$validationRules=[
	            'quelle_id' => 'required'
	        ];
	        $validator= \Validator::make($input, $validationRules);
	        if($validator->fails()){
	            $returnArr['status']=3;
	            $returnArr['content']=$validator->errors();
	            $returnArr['message']="Validation failed, quelle_id not provided";
	            return $returnArr;
	        }

	        if (is_array($input['quelle_id'])) 
    		{
    			DB::table('quelle_autor')->whereIn('quelle_id', $input['quelle_id'])->delete();
    			DB::table('quelle_pruefer')->whereIn('quelle_id', $input['quelle_id'])->delete();
    			$resultData=$this->quelle->whereIn('quelle_id', $input['quelle_id'])->delete();
	    		if($resultData){
		        	$returnArr['status']=2;
			        $returnArr['content']="";
			        $returnArr['message']="Quelle(n) deleted successfully";
		        }else{
			    	$returnArr['status']=5;
	                $returnArr['content']="";
	                $returnArr['message']="Operation failed, could not delete the quelle(n). Please check the provided quelle_id(s)";
		        }
    		}
    		else
    		{
    			$quelleData=$this->quelle->where('quelle_id', $input['quelle_id'])->where('quelle_type_id', 1)->first();
		        if($quelleData === null){
		        	$returnArr['status']=4;
			        $returnArr['content']="";
			        $returnArr['message']="No Quelle found with provided quelle_id";
		        }else{
		        	DB::table('quelle_autor')->where('quelle_id', $input['quelle_id'])->delete();
		        	DB::table('quelle_pruefer')->where('quelle_id', $input['quelle_id'])->delete();
		        	$resultData=$this->quelle->where('quelle_id', $input['quelle_id'])->delete();
			        if($resultData){
			        	$returnArr['status']=2;
				        $returnArr['content']="";
				        $returnArr['message']="Quelle deleted successfully";
			        }else{
				    	$returnArr['status']=5;
		                $returnArr['content']="";
		                $returnArr['message']="Operation failed, could not delet the Quelle";
			        }
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

	/**
    * Get quelle schemas Method
    * fetching all predefined quelle schemas
    * Return : all predefine quelle schemas
    **/
    public function getPreDefinedQuelleSchemas(){
    	$returnArr=config('constants.return_array');
		try{
			$result['data']=config('constants.quelle_schemas');
        	$returnArr['status']=2;
            $returnArr['content']=$result;
            $returnArr['message']="quelle schemas fetch successfully";
		}
    	catch(\Exception $e){
        	$returnArr['status']=6;
	        $returnArr['content']=$e;
	        $returnArr['message']="Something went wrong";
        }

        return $returnArr; 
    }
}
