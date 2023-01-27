<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers as CustomHelper;

class QuelleGradingSettings extends Model
{
    protected $table = 'quelle_grading_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quelle_id', 'normal', 'normal_within_parentheses', 'normal_end_with_t', 'normal_end_with_tt', 'normal_begin_with_degree', 'normal_end_with_degree', 'normal_begin_with_asterisk', 'normal_begin_with_asterisk_end_with_t', 'normal_begin_with_asterisk_end_with_tt', 'normal_begin_with_asterisk_end_with_degree', 'sperrschrift', 'sperrschrift_begin_with_degree', 'sperrschrift_begin_with_asterisk', 'sperrschrift_bold', 'sperrschrift_bold_begin_with_degree', 'sperrschrift_bold_begin_with_asterisk', 'kursiv', 'kursiv_end_with_t', 'kursiv_end_with_tt', 'kursiv_begin_with_degree', 'kursiv_end_with_degree', 'kursiv_begin_with_asterisk', 'kursiv_begin_with_asterisk_end_with_t', 'kursiv_begin_with_asterisk_end_with_tt', 'kursiv_begin_with_asterisk_end_with_degree', 'kursiv_bold', 'kursiv_bold_begin_with_asterisk_end_with_t', 'kursiv_bold_begin_with_asterisk_end_with_tt', 'kursiv_bold_begin_with_degree', 'kursiv_bold_begin_with_asterisk', 'kursiv_bold_begin_with_asterisk_end_with_degree', 'fett', 'fett_end_with_t', 'fett_end_with_tt', 'fett_begin_with_degree', 'fett_end_with_degree', 'fett_begin_with_asterisk', 'fett_begin_with_asterisk_end_with_t', 'fett_begin_with_asterisk_end_with_tt', 'fett_begin_with_asterisk_end_with_degree', 'gross', 'gross_begin_with_degree', 'gross_begin_with_asterisk', 'gross_bold', 'gross_bold_begin_with_degree', 'gross_bold_begin_with_asterisk', 'pi_sign', 'one_bar', 'two_bar', 'three_bar', 'three_and_half_bar', 'four_bar', 'four_and_half_bar', 'five_bar', 'active', 'ip_address', 'stand', 'bearbeiter_id', 'ersteller_datum', 'ersteller_id'
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
