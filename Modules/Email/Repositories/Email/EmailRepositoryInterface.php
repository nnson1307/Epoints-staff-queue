<?php


namespace Modules\Email\Repositories\Email;


interface EmailRepositoryInterface
{
    /**
     * Job gửi mail
     * @return mixed
     */
    public function sendEmail();
}