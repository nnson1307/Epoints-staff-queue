<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class KpiNoteDetailTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "kpi_note_detail";
    protected $primaryKey = "kpi_note_detail_id";


    const IS_CLOSE = "D";
    const NOT_DELETED = 0;
    const NOT_CALCULATE_COMMISSION = 0;

    /**
     * Lấy kpi đã chốt để tính hoa hồng
     *
     * @param $noteType
     * @param $noteTypeId
     * @param $kpiCriteriaId
     * @return mixed
     */
    public function getKpiClosing($noteType, $noteTypeId, $kpiCriteriaId)
    {
        $ds = $this
            ->select(
                "{$this->table}.kpi_note_detail_id",
                "n.kpi_note_name",
                "{$this->table}.kpi_criteria_id",
                "{$this->table}.kpi_value",
                "{$this->table}.staff_id",
                "n.team_id",
                "n.branch_id",
                "n.department_id",
                "n.kpi_note_type",
                "n.status",
                "c.calculate_kpi_total_id",
                "c.original_total_percent",
                "c.weighted_total_percent"
            )
            ->join("kpi_note as n", "n.kpi_note_id", "=", "{$this->table}.kpi_note_id")
            ->join("calculate_kpi_total as c", "c.kpi_note_detail_id", "=", "{$this->table}.kpi_note_detail_id")
            ->where("n.kpi_note_type", $noteType)
            ->where("n.status", self::IS_CLOSE)
            ->where("{$this->table}.is_calculate_commission", self::NOT_CALCULATE_COMMISSION);

        //Lấy theo tiêu chí
        if ($kpiCriteriaId != null && $kpiCriteriaId != 0) {
            $ds->where("{$this->table}.kpi_criteria_id", $kpiCriteriaId);
        }

        //Lấy theo giá trị của (cá nhân - nhóm - phòng ban - chi nhánh)
        if ($noteType != null) {
            switch ($noteType) {
                case 'S':
                    $ds->where("{$this->table}.staff_id", $noteTypeId);
                    break;
                case 'T':
                    $ds->where("n.team_id", $noteTypeId);
                    break;
                case 'B':
                    $ds->where("n.branch_id", $noteTypeId);
                    break;
                case 'D':
                    $ds->where("n.department_id", $noteTypeId);
                    break;
            }
        }

        return $ds->get();
    }

    /**
     * Chỉnh sửa chi tiết phiếu giao kpi
     *
     * @param array $data
     * @param $kpiNoteDetailId
     * @return mixed
     */
    public function edit(array $data, $kpiNoteDetailId)
    {
        return $this->where("kpi_note_detail_id", $kpiNoteDetailId)->update($data);
    }
}