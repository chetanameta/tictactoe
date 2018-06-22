var brddata = [
    ["", "", ""],
    ["", "", ""],
    ["", "", ""]
];
function drawGameBoard(boardData, win) {
    var size = boardData.length;
    var rootDiv = $('.gameboard');
    $(rootDiv).html('');
    for (i = 0; i < size; i++) {
        $('<div>').addClass('row').attr('id', 'row-' + i).appendTo(rootDiv);
        for (j = 0; j < boardData[i].length; j++) {
            var dynamicClass = '';
            if (boardData[i][j] == 'X') {
                dynamicClass = 'cross';
            } else if (boardData[i][j] == 'O') {
                dynamicClass = 'circle';
            }
            $('<div>').addClass('item').attr('id', 'row-' + i + '-item-' + j).appendTo($('#row-' + i));
            if (dynamicClass) {
                $('<div>').addClass(dynamicClass).appendTo('#row-' + i + '-item-' + j);
            } else {
                if (win == false) {
                    $('<div>').addClass('emptyCell').attr('onclick', 'javascript: selectMove(this)').appendTo('#row-' + i + '-item-' + j);
                }

            }
        }
    }
}

function selectMove(element) {
    var parent = $(element).parent();
    parent.html('<div class="cross"></div>');
    brddata = buildBoard();
    $('#message').html("X Played.");
    var newData = JSON.stringify(brddata);
    $.post('tictactoe.php', {board: newData}, function (data) {
        jsonData = $.parseJSON(data);
        var win = false;
        setTimeout(function () {
            $('#message').html(jsonData.message);
            if (jsonData.message.indexOf('wins') != -1) {
                win = true;
            }
            drawGameBoard(jsonData.board, win);
        }, 500);
    });
}

function buildBoard() {
    var brddata1 = brddata;
    $('.gameboard').find('.item').each(function () {
        var index_id = $(this).attr('id');
        var indexes = index_id.split('-');
        if ($(this).find('.circle').length) {
            brddata1[indexes[1]][indexes[3]] = 'O';
        }
        if ($(this).find('.cross').length) {
            brddata1[indexes[1]][indexes[3]] = 'X';
        }
    });
    return brddata1;
}

$(document).ready(function () {
    drawGameBoard(brddata, false);
});