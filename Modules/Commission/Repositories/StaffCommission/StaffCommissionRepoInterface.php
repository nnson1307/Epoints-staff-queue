<?php

namespace Modules\Commission\Repositories\StaffCommission;

interface StaffCommissionRepoInterface
{
    /**
     * Tính hoa hồng cho nhân viên
     *
     * @return mixed
     */
    public function calculateStaffCommission();
}