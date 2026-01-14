<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class UccFilings
 *
 * @package App\Models
 */
class UccAssignees extends Model
{
    protected static $table = "ucc_assignees";

    public static function get_or_insert($partner_id, $assignee)
    {
        # Return ID if exists
        $finder = self::where('id',$assignee->assignee_id)->first();
        if ($finder) return $finder->id;

        # If assignee_id is empty, return null
        if (empty($assignee->assignee_id)) return null;

        # Otherwise, insert
        self::insert([
            'id'               => $assignee->assignee_id,
            'partner_id'       => $partner_id,
            'assignee_class'   => $assignee->assignee_class,
            'assignee_company' => $assignee->assignee_company,
            'assignee_city'    => $assignee->assignee_city,
            'assignee_state'   => $assignee->assignee_state,
        ]);
        return $assignee->assignee_id;
    }
}