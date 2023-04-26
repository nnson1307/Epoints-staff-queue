<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class KpiNoteTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "kpi_note";
    protected $primaryKey = "kpi_note_id";

    const IS_CLOSE = "D";
    const NOT_DELETED = 0;
    const IS_CALCULATE_COMMISSION = 1;

    /**
     * Lấy phiếu giao kpi
     *
     * @param $noteType
     * @return mixed
     */
    public function getKpiNoteClosing($noteType)
    {
        return $this
            ->select(
                "{$this->table}.kpi_note_id",
                "{$this->table}.kpi_note_name",
                "{$this->table}.effect_year",
                "{$this->table}.effect_month",
                "{$this->table}.branch_id",
                "{$this->table}.department_id",
                "{$this->table}.team_id",
                "{$this->table}.kpi_note_type",
                "{$this->table}.status"
            )

            ->where("{$this->table}.status", self::IS_CLOSE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("{$this->table}.kpi_note_type", $noteType)
            ->get();
    }
}