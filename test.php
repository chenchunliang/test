<?php

require_once("functions/functions.php");
header("<metadata charset=utf8>");

$pdo= new functions("test");//DBname

//******************select**********************
//傳送來的資料

//呼叫並執行sql方法
$where = array("admin_account"=>"B102%");//下條件
$rs=$pdo->select("admin",$where);//資料表，條件式
echo "<p>1.只抓第1筆資料：「".$rs[1]['admin_name']."」</p>";//只抓第1筆資料

//顯示所有查詢結果
echo "<p>2.ssss顯示所有查詢結果：</p>";
echo "「";
foreach($rs as $key){ //rs 2D => $key 1D
echo "<p>".$key['admin_name'].":".$key['admin_account']."，".$key['admin_password']."</p>";
}
echo "」";
echo $pdo->error();
/*
*/
//**********************************************


//******************insert**********************
/*
$post=array("admin_account"=>"B10221141","admin_password"=>"B10221141","admin_name"=>"陳國軒");
$rs=$pdo->insert("admin",$post);      //資料表，資料表內容
echo "新增後的id：「".$rs."」";

//$pdo->error();
*/
//**********************************************



//******************update**********************
/*
$post=array("admin_name"=>"陳俊良");
$rs=$pdo->update("admin",$post,2);   //資料表，資料表內容，ID
echo ($rs)?"更新成功":"更新失敗";

//$pdo->error();
*/
//**********************************************



//******************delete**********************
/*
$rs=$pdo->delete("admin",2);         //資料表，ID
echo ($rs)?"刪除成功":"刪除失敗";
//$pdo->error();
*/
//**********************************************
?>