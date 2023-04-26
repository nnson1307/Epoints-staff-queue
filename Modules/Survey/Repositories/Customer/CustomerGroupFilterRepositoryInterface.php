<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 10/23/2019
 * Time: 4:02 PM
 */

namespace Modules\Survey\Repositories\Customer;


interface CustomerGroupFilterRepositoryInterface
{
    public function importExcel($file, $arrayPhoneExist);

    public function searchWhereInUser(array $data = []);

    public function searchAllCustomer(array $filters = []);

    public function addCustomerGroupDefine(array $data = []);

    public function submitAddGroupDefine(array $data = []);

    public function list(array $filters = []);

    public function getCustomerByGroupDefine($id);

    public function getItem($id);

    public function updateCustomerGroupDefine(array $data = []);

    public function getCondition(array $data = []);

    public function getCustomerGroupDefine();

    public function getListAllService();

    public function getListAllProduct();

    public function getListAllServiceCard();

    public function getListAllRank();

    public function submitAddAutoAction(array $data = []);

    public function submitEditAutoAction(array $data = []);

    public function getCustomerGroupDetail($id);

    public function getCustomerInGroupAuto($id);
    
    public function getCustomerInGroup($id);

    public function getOptionByType($type);

    public function deleteGroupAuto($id);

    public function deleteGroupDefine($id);
}