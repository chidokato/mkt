<?php

namespace App\Helpers;

class TreeHelper
{
    public static function buildOptions(
        $items,
        $parentId = 0,
        $prefix = '',
        $selectedId = null,
        $idField = 'id',
        $parentField = 'parent',
        $nameField = 'name'
    ) {
        $html = '';

        foreach ($items as $item) {
            if ((int)$item->$parentField === (int)$parentId) {

                $selected = ((string)$selectedId === (string)$item->$idField) ? 'selected' : '';

                $html .= "<option value='{$item->$idField}' {$selected}>{$prefix}{$item->$nameField}</option>";

                $html .= self::buildOptions(
                    $items,
                    $item->$idField,
                    $prefix . '— ',
                    $selectedId,
                    $idField,
                    $parentField,
                    $nameField
                );
            }
        }

        return $html;
    }

    /**
     * Lấy danh sách ID của department con cháu theo đệ quy (kèm chính nó nếu includeSelf=true)
     * @param \Illuminate\Support\Collection|array $items  (đã load 1 lần)
     */
    public static function descendantIds(
        $items,
        int $rootId,
        bool $includeSelf = true,
        string $idField = 'id',
        string $parentField = 'parent'
    ): array {
        // build map parent => [childIds...]
        $childrenMap = [];

        foreach ($items as $it) {
            $p = (int)($it->$parentField ?? 0);
            $childrenMap[$p][] = (int)$it->$idField;
        }

        $result = [];
        $queue  = [$rootId];

        if ($includeSelf) {
            $result[] = $rootId;
        }

        while ($queue) {
            $current = array_shift($queue);

            foreach ($childrenMap[$current] ?? [] as $childId) {
                $result[] = $childId;
                $queue[]  = $childId;
            }
        }

        return array_values(array_unique($result));
    }
}



class TreeHelper_disabled
{
    public static function buildDepartmentOptions($items, $parent = 0, $prefix = '', $selectedId = null)
    {
        $html = '';

        foreach ($items as $item) {
            if ($item->parent == $parent) {

                // kiểm tra nếu có con → disable
                $disabled = $item->children()->exists() ? 'disabled' : '';

                // chọn option nếu đúng user đang dùng
                $selected = ($selectedId == $item->id) ? 'selected' : '';

                $html .= "<option value='{$item->id}' {$disabled} {$selected}>{$prefix}{$item->name}</option>";

                // đệ quy xuống cấp dưới
                $html .= self::buildDepartmentOptions($items, $item->id, $prefix . '-- ', $selectedId);
            }
        }

        return $html;
    }
}


class TreeHelperLv2Only
{
    public static function buildOptions(
        $items,
        $selectedId = null,
        $idField = 'id',
        $parentField = 'parent',
        $nameField = 'name'
    ) {
        $html = '';

        // LV1
        foreach ($items as $lv1) {
            if ($lv1->$parentField == 0) {

                $selected = $selectedId == $lv1->$idField ? 'selected' : '';
                $html .= "<option value='{$lv1->$idField}' {$selected}>{$lv1->$nameField}</option>";

                // LV2
                foreach ($items as $lv2) {
                    if ($lv2->$parentField == $lv1->$idField) {

                        $selected = $selectedId == $lv2->$idField ? 'selected' : '';
                        $html .= "<option value='{$lv2->$idField}' {$selected}>— {$lv2->$nameField}</option>";

                        // ❌ KHÔNG render LV3
                    }
                }
            }
        }

        return $html;
    }
}
