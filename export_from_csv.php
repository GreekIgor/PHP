function Start()
{
$start = microtime(true);
echo 'Скрипт запущен...'.PHP_EOL;

$customers = get_customer();
echo 'Список абонентов получен'.PHP_EOL;

for ($i=0; $i < sizeof($customers); $i++) { 
	# code...
$customer = $customers[$i];
$customer_arr_id = $customer['customer_arr_id'];
//$balance_arr = $customer['user_balance'];

$subscr_arr = [];
$equipment_arr = [];

	foreach ($customer_arr_id as $cust_arr_id) {

		$equipments = [];
		$equipments = GetEquipmentCustomer($cust_arr_id, $customer['ADDR']);
        $subscrs = [];
		$subscrs = 	GetServices($cust_arr_id,$customer['dogovor_no'],$customer, $equipments);
	    $subscr_arr= array_merge($subscr_arr, $subscrs); 
	    $equipment_arr =  array_merge($equipment_arr, $equipments); 

	}

/*$balance = 0;
  foreach ($balance_arr as $balance_user) {
  	# code...
  	echo $balance_user.PHP_EOL;
  	$balance = $balance + $balance_user;
  }*/

 $customers[$i]['subscrs'] =  $subscr_arr;
 $customers[$i]['equipments'] = $equipment_arr;
 $customers[$i]['BALANCE'] = $balance;

 echo $customer['ADDR'].' '.$customer['NAME'].'  '.$customer['BALANCE'].PHP_EOL;


}



//var_dump($customers);


// генерация файлов доступа
$f_users =  GetGenFile("USERS");
$f_account =  GetGenFile("ACCOUNTS");
$f_group_link =  GetGenFile("GROUPLINK");
$f_equips =  GetGenFile("EQUIP");
$f_net_serv =  GetGenFile("NETSERV");
$f_contract =  GetGenFile("CONTRACTS");
$f_subscrs =  GetGenFile("SUBSCR");

$f_opequip =  GetGenFile("OP_EQUIP");
//*************

WriteHeaderOP_EQUIP($f_opequip);
WriteHeaderUSER($f_users);
WriteHeaderACCOUNTS($f_account);
WriteHeaderGROUPLINK($f_group_link);
WriteHeaderEQUIP($f_equips);
WriteHeaderNETSERV($f_net_serv);
WriteHeaderCONTRACTS($f_contract);
WriteHeaderSUBSCR($f_subscrs);



// Получить список операторского оборудования
$operator_equipments = GetOperatorEquipment();

	foreach ($operator_equipments as $operator_equipment) {
	WriteOP_EQUIP($f_opequip, $operator_equipment);
	}

    foreach ($customers as $user) {
     $subscrs = $user['subscrs'];


     if(sizeof($subscr)){
     WriteUSERS($f_users, $user); // таблица абонентов
     WriteACCOUNTS($f_account, $user);} // лицевые счета абонентов
     else echo $user['ADDR'].PHP_EOL;
   
     $equipments = $user['equipments'];
    // занесение всего оборудования абонента
     foreach($equipments as $equip)
     {
     	if(sizeof($subscr)){ $ID_EQUIP = WriteEQUIP($f_equips, $equip); } // оборудование абонента
     	if($user['id_netserv']<>$prev_id)
    										{
      	if($equip['EQUIP_TYPE_ID'] == 1) {  if(sizeof($subscr)){ WriteNETSERV($f_net_serv , $user, $ID_EQUIP); } }  //если это IP адрес логины пароли для интернета
      	    $prev_id = $user['id_netserv'];
      										}
     }
     //*************
     
    // запись подписок абонентов на услуги
     foreach($subscrs as $subscr)
     {
     	WriteSUBSCR($f_subscrs, $subscr, $user); // подписки абонента на услуги
     	WriteGROUPLINK($f_group_link, $user, $subscr); // участие абонента в группах для скидок
     	
     }
     //*************
     if(sizeof($subscr)){
      WriteCONTRACTS($f_contract, $user); } // договора абонентов


}



fclose($f_users);
fclose($f_account);
fclose($f_group_link);
fclose($f_equips);
fclose($f_net_serv);
fclose($f_contract);
fclose($f_subscrs);
fclose($f_opequip);
$finish = microtime(true);

$delta = $finish - $start;

echo 'Время выполнения скрипта - '.$delta.' сек '.(ceil($delta/60)).' мин';

}
