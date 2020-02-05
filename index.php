<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

// $context = array(
//     'ssl' => array(
//         'local_cert'  => '/opt/crt/certificate.crt',
//         'local_pk'    => '/opt/new/www_omegacoding_com.key',
//         'verify_peer' => false,
//     )
// );

$port = '3773';
$io = new SocketIO($port);
// $io = new SocketIO($port, $context);


$io->on('connection', function ($socket) use ($io) {
  echo ".\n\r.\n\r.\n\r";
  echo ">++ $socket->id.simom --- new user has connected \n";
  $socket->addedUser = false;
  $socket->connectedUser = false;

  // var_dump($socket->username);

  $socket->on('connect confirm', function ($confirmArray) use ($socket) {
    global $users, $status;
    echo $confirmArray['connectId'];
    // if(!empty($confirmArray['connectId'])){
    $connectId = $confirmArray['connectId'];
    $socket->connectedUserConnectId = $connectId; // store in session current connection
    $users[$connectId] = $confirmArray;
    $socket->connectedUser = true;
    echo ('confirmArray--' . json_encode($confirmArray) . "\n");
    echo ("--users: confirm--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
  // }
  //   else {echo "datark";}
  });

  $socket->on('add user', function($userdata) use($socket){
    global $users, $userNumber, $status;
    ++$userNumber;
    $status='free';
    $connectId = $userdata['connectId'];
    $socket->addedUserConnectId = $connectId; // store in session current addition
    $users[$connectId] = $userdata;
    $users[$connectId]['userNumber'] = $userNumber;
    $users[$connectId]['status'] = $status;
    $socket->addedUser = true;
    
    // here $socket->broadcast->emit usersList
    echo ("userdata--\n" . json_encode($userdata, JSON_PRETTY_PRINT) . "\n");
    echo ("--users: add--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
    // echo ('------------------'.count($users));
    $connect_users=array();
    $connect_users1=array();

    if(count($users)>1){
    
           $connectId=$users[$connectId]['connectId'];
           $gameId=$users[$connectId]['gameId'];
           $betPrice=$users[$connectId]['betPrice'];
           $object1="";
        foreach($users as $key=>$value) {
                  if($value['connectId']==$connectId){
                    $user11=$value;
                    $idid=$value['connectId'];
                  }
              if($value['connectId']!==$connectId && $value['gameId']==$gameId ){
                   array_push($connect_users, $value);

                    echo ("--one: user--\n" . json_encode($value, JSON_PRETTY_PRINT) . "\n");
              }
        }
                 $rev_connect_users=array_reverse($connect_users);

        echo ("--+++array: array++++--\n" . json_encode($rev_connect_users, JSON_PRETTY_PRINT) . "\n");
        foreach ($rev_connect_users as $key1 => $value1) {
                  if($betPrice==$value1['betPrice']){
                    $object_value=$value1;
                    $object1=1;
                    
                    // echo "=";
                       // echo ("--bet===price: user--\n" . json_encode($value, JSON_PRETTY_PRINT) . "\n");
        //                $object = array_reduce($rev_connect_users, function($a, $b){
        //                  return $a['userNumber'] < $b['userNumber'] ? $a : $b;
        //                 }, array_shift($rev_connect_users));
        // echo ("--+++obj===: obj++++--\n" . json_encode($object_value, JSON_PRETTY_PRINT) . "\n");

                  }
                  else{
                    $object1=2;
                    array_push($connect_users1, $value1);
                    // echo "e";
                        // $object = array_reduce($rev_connect_users, function($a, $b){
                        //  return $a['betPrice'] < $b['betPrice'] ? $a : $b;
                        // }, array_shift($rev_connect_users));


        }
        
        
  }
   echo ("--+++user1 array: ++++--\n" . json_encode($connect_users1, JSON_PRETTY_PRINT) . "\n");
  
   if(empty($object_value)){
         $object = array_reduce($connect_users1, function($a, $b){
                         return $a['betPrice'] < $b['betPrice'] ? $a : $b;
                        }, array_shift($rev_connect_users));
                echo ("--+++obj: obj++++--\n" . json_encode($object, JSON_PRETTY_PRINT) . "\n");
    }
    else{
              $users[$connectId]['status']='busy';

              $object_value['status']='busy';
                // $value['status']='busy';
              echo ("--+++obj===: obj++++--\n" . json_encode($object_value, JSON_PRETTY_PRINT) . "\n");
              $tt=json_encode($users[$connectId]);
          // $socket->to($connectId)->emit('resum2',$object_value);
          $socket->to($object_value['connectId'])->emit('resum2',$tt);
          $k=json_encode($object_value);
          $socket->emit('resum2', $k);
          echo ("--+++array: array++++--\n" . json_encode($rev_connect_users, JSON_PRETTY_PRINT) . "\n");
    }
        echo $object1;
    }
  else{
    echo "non user";
    $socket->emit('no users',"no");
   }


  });

  $socket->on('new message', function ($msg) use ($io) {
    $io->emit('new message', $msg);
  });

  $socket->on('disconnect', function() use($socket) {
    global $users;
    if($socket->addedUser) {
      unset($users[$socket->addedUserConnectId]);
      // here $socket->broadcast->emit usersList
      echo ("--users: left--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
    }

    if($socket->connectedUser) {
      unset($users[$socket->connectedUserConnectId]);
      echo ("--users: left without adding--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
    }
  });
});

Worker::runAll();
