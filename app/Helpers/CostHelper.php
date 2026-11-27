<?php 

use App\Models\Department;

if (!function_exists('sumDepartmentCost')) {
    function sumDepartmentCost($departmentId, $taskByDepartment)
    {
        $ids = Department::getChildIds($departmentId);

        $gross = 0;
        $net = 0;

        foreach ($ids as $id) {
            if (isset($taskByDepartment[$id])) {
                $gross += $taskByDepartment[$id]->gross_cost;
                $net += $taskByDepartment[$id]->net_cost;
            }
        }

        return [
            'gross' => $gross,
            'net' => $net,
            'support' => $gross - $net,
        ];
    }
}


?>