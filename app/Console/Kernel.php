<?php

namespace App\Console;

use Modules\ManageWork\Console\JobUpdateProject;
use Modules\Survey\Console\SurveyNotifi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        JobUpdateProject::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // TODO: Lấy connection string mới
        $schedule->command('epoint:connection_string')
            ->cron('*/30 * * * *');

        //Nhắc nhở Quản lý công việc
        $schedule->command('manageWork:sendRemindWork')
            ->everyMinute();
        //Tần suất lặp lại công việc
        $schedule->command('manageWork:repeatWork')
            ->everyFiveMinutes();
        $schedule->command('manageWork:notiTicketOverdue')
            ->everyFiveMinutes();
        $schedule->command('manageWork:notiTicketNotAssign')
            ->everyFiveMinutes();
        $schedule->command('manageWork:workOverdue')
            ->everyFiveMinutes();
        $schedule->command('email:sendMailJob')
            ->everyFiveMinutes();
        $schedule->command('epoint:send_contract_staff_notification')
            ->everyFiveMinutes();
        $schedule->command('shift:remindCheckin')
            ->everyMinute();

        //Job tính hoa hồng cho nhân viên
        $schedule->command('commission:calculate-staff')
            ->dailyAt('01:00');
        //Job nhắc nhở HĐ sắp đến hạn thu-chi
        $schedule->command('epoint:remind-expected-revenue')
            ->dailyAt('07:00');
        //Job nhắc nhở HĐ đến hạn thu chi
        $schedule->command('epoint:due-receipt-spend')
            ->dailyAt('07:15');
        //Job nhắc nhở HĐ sắp hết hạn
        $schedule->command('epoint:contract-coming-end')
            ->dailyAt('07:30');
        //Job nhắc nhở HĐ đã hết hạn
        $schedule->command('epoint:contract-expired')
            ->dailyAt('07:45');
        //Job nhắc nhở HĐ sắp hết hạn bảo hành
        $schedule->command('epoint:warranty-coming-end')
            ->dailyAt('08:00');
        //Job nhắc nhở HĐ đến hạn bảo hành
        $schedule->command('epoint:warranty-expired')
            ->dailyAt('08:15');
        //Job nhắc nhở nhân viên chưa có CV hoặc update trạng thái (note giùm a Phú)
        $schedule->command('manageWork:workStartDay')
            ->dailyAt('08:30');
        //Thông báo nhân viên chưa có công việc trong ngày hoặc chưa bắt đầu công việc
        $schedule->command('manageWork:workNotiEveryDay')
            ->dailyAt('10:00');

        // Send noti khảo sát
        $schedule->command('epoint:survey_apply_user')->everyFiveMinutes();
        //Send noti khảo sát tính điểm
        $schedule->command('epoint:survey_count_point')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
