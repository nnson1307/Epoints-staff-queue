<?php
namespace App\Console\Commands;

use App\Models\BrandTable;
use DaiDP\StsSDK\TenantManagement\TenantManagementInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use MyCore\FileManager\Stub;
use MyCore\Helper\OpensslCrypt;
/**
 * Class GetConnectionStringCmd
 * @package App\Console\Commands
 * @author DaiDP
 * @since Sep, 2019
 */
class GetConnectionStringCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epoint:connection_string';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Lấy connection string từ Azure cache về máy.';


    /**
     * Execute the console command
     *
     * @param TenantManagementInterface $umSDK
     */
    public function handle()
    {
        $mPiospaBrand = new BrandTable();
        $arrBrand = $mPiospaBrand->getAllBrand();

        $oConstr = new OpensslCrypt(env('OP_SECRET'), env('OP_SALT'));

        $arrConnStr = [];
        foreach ($arrBrand as $brand){
            $arrConnStr[ $brand['tenant_id'] ] = $oConstr->decode($brand['brand_contr']);
        }

        // Lay check sum
        $beforeMd5 = $this->getMd5();

        // Ghi ra file cấu hình và save vào config
        $oStub = new Stub(resource_path('stubs/epoint-connstr.stub'), [
            'CONN_STR' => var_export($arrConnStr, true)
        ]);
        $oStub->saveTo(config_path(), 'epoint-connstr.php');

        $afterMd5 = $this->getMd5();

        if ($beforeMd5 != $afterMd5) {
            $this->line('Restart Queue.');
            Artisan::call('queue:restart');
        }

        $this->line('ok');
    }

    /**
     * Lay check sum
     *
     * @return string
     */
    protected function getMd5()
    {
        $path = config_path('epoint-connstr.php');

        return md5_file($path);
    }
}
