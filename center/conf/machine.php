<?php
return array(
    '/center/machine/getmachineshop'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"getmachineshop"]),
        "comment"=>"获得时光机列表",
    ],
    '/center/machine/machinebuy'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"machinebuy"]),
        "comment"=>"购买时光机",
    ],
    '/center/machine/machinedel'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"machinedel"]),
        "comment"=>"销毁时光机",
    ],
    '/center/machine/getmachinelist'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"getmachinelist"]),
        "comment"=>"获得我的时光机列表",
    ],
    '/center/machine/machineupgrade'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"machineupgrade"]),
        "comment"=>"升级时光机",
    ],
    '/center/machine/machinepledge'=>[
        "access_by_php_class"=>[['class'=>'scripts\\Access','method'=>'access',"params"=>[]],],
        "content_by_php_class"=>array('class'=>'scripts\\MachineCtrl','method'=>'invoke',"params"=>["fname"=>"machinepledge"]),
        "comment"=>"质押",
    ],
);
