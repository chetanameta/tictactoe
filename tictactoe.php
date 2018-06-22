<?php

include('vendor/autoload.php');

$tictactoe = new ChetanAmeta\Tictactoe(3);

if(isset($_POST['board'])){

    $board = json_decode($_POST['board']);
    $nextMove = $tictactoe->makeMove($board ,'O');

    if(!empty($nextMove)){
        $board[$nextMove[1]][$nextMove[0]] = $nextMove[2];
    }
    $response = ['message'=> $tictactoe->getMessage(), 'board'=>$board];
    echo json_encode($response);
}
?>