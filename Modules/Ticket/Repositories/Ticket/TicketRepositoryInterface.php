<?php


namespace Modules\Ticket\Repositories\Ticket;


interface TicketRepositoryInterface
{

    /**
     * Ticket quá hạn
     * @return mixed
     */
    public function ticketOverdue();

    /**
     * Ticket chưa phân công
     * @return mixed
     */
    public function ticketNotAssign();
}