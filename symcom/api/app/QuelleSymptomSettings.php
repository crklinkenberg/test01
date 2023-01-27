<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers as CustomHelper;

class QuelleSymptomSettings extends Model
{
    protected $table = 'quelle_symptom_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quelle_id', 'symptom_type_for_whole', 'symptoms_with_reference', 'symptoms_without_reference', 'symptoms_with_provers', 'symptoms_without_provers', 'symptom_with_A_f_d_H', 'active', 'ip_address', 'stand', 'bearbeiter_id', 'ersteller_datum', 'ersteller_id'
    ];

    public $timestamps = false;

    /**
     * Appending creator name and editor name in the return array
     */
    protected $appends = ['ersteller', 'bearbeiter'];

    /**
     * The Quelle that belong to the quelle grading settings.
     */
    public function quelle()
    {
        return $this->belongsTo('App\Quelle');
    }

    /**
     * Geting Created at date time in project format
     */
    public function getErstellerDatumAttribute($value)
    {
        $datetimeFormat=config('constants.date_time_format');
        return ($value != "" and $value != NULL and $value != '0000-00-00 00:00:00') ? \Carbon\Carbon::parse($value)->format($datetimeFormat) : NULL;
    }

    /**
     * Geting Updated at date time in project format
     */
    public function getStandAttribute($value)
    {
        $datetimeFormat=config('constants.date_time_format');
        return ($value != "" and $value != NULL and $value != '0000-00-00 00:00:00') ? \Carbon\Carbon::parse($value)->format($datetimeFormat) : NULL;
    }

    /**
     * Geting Creator name
     */
    public function getErstellerAttribute()
    {
        $creatorName=CustomHelper::getUserData($this->ersteller_id, 'full_name');
        return ($creatorName != "") ? $creatorName : NULL;
    }

    /**
     * Geting Editor name
     */
    public function getBearbeiterAttribute()
    {
        $editorName=CustomHelper::getUserData($this->bearbeiter_id, 'full_name');
        return ($editorName != "") ? $editorName : NULL;
    }

}
