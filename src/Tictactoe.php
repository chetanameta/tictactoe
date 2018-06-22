<?php
/**
 * Created by PhpStorm.
 * User: Chetan Ameta
 * Date: 8.01.2017
 * Time: 11:30
 */

namespace ChetanAmeta;

/**
 * Class Tictactoe
 * @package ChetanAmeta
 */
class Tictactoe implements MoveInterface
{

    /**
     * Keeps the board's array. Should be NxN
     *
     * @var array
     */
    private $ticTacToeBoard;

    /**
     * Used by constructor for creating an empty array.
     *
     * @var int
     */
    private $boardSize = 3;

    /**
     * Board Size -1. Used for readability.
     *
     * @var int
     */
    private $maxIndex;

    /**
     * Class returns a message in each step. Winning is also detected by message.
     *
     * @var
     */
    public $message;

    /**
     * Keeps an array, containing x and y coordinates for next move, and th
     * e unit that now occupies it.
     * Example: [2, 0, 'O'] - upper right corner - O player
     *
     * @var array
     */
    private $nextMove = [];

    /**
     * Tictactoe constructor.
     * @param int $boardSize
     */
    public function __construct($boardSize)
    {
        $this->boardSize = $boardSize;
        $this->maxIndex = $boardSize - 1;
    }

    /**
     * Helper method for setting message.
     *
     * @param string $message
     */
    private function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get's the injected boardState, checks by counting diagonals, columns and rows.
     * If won, returns an empty array.
     *
     * @param array $boardState
     * @param string $playerUnit
     * @return array
     */
    public function makeMove($boardState, $playerUnit = 'X')
    {
        $this->setTicTacToeBoard($boardState);
        // Check if opponent won.
        $winningUnit = ($playerUnit == 'X') ? 'O' : 'X';

        $result = $this->checkWinning($winningUnit);

        if (!$result) {
            $this->nextMove = [];
            $this->message = "";

            $result = $this->checkWinning($playerUnit);

            if (!$result && !empty($this->nextMove)) {
                $this->ticTacToeBoard[$this->nextMove[1]][$this->nextMove[0]] = $this->nextMove[2];
                $this->checkWinning($playerUnit);
            }
            if (empty($this->nextMove)) {
                $this->setMessage("No one wins.");
            }
            return $this->nextMove;
        }
        return [];
    }

    /**
     * Serves to inject the boardState to tictactoe object.
     *
     * @param array $tictactoeboard
     * @return bool
     */
    private function setTicTacToeBoard($tictactoeboard)
    {
        if (sizeof($tictactoeboard) == $this->boardSize && sizeof($tictactoeboard[0]) == $this->boardSize) {
            $this->ticTacToeBoard = $tictactoeboard;
            return true;
        }
        $this->setMessage("Board size is wrong.");
        return false;
    }


    private function getFirstEmptyAdjscentElement($y, $x)
    {
        $coordinateList = $this->findNeighboorKeys($this->boardSize, $y, $x);
        if ($coordinateList) {
            $board = $this->ticTacToeBoard;
            //find first empty element in board
            $firstEmptyKeys = array_filter($coordinateList, function ($keys) use ($board) {
                return $board[$keys[0]][$keys[1]] == "";
            });
        }
        return (isset($firstEmptyKeys[0])) ? $firstEmptyKeys[0] : [];
    }

    /**
     * Defines next move if diagonals, columns and rows have only one count
     * of the player unit or the opponent.
     *
     * If block set to true, counts diagonals, rows and columns for the opponent.
     *
     * @param string $playerUnit
     * @param bool $block
     * @return bool
     */
    public function nextMove($playerUnit = 'X', $block = false)
    {
        $player = $playerUnit;

        if ($block) {
            $playerUnit = ($playerUnit == 'X') ? 'O' : 'X';
        }

        $rightDiagonalSum = 0;
        $leftDiagonalSum = 0;

        for ($i = 0; $i <= $this->maxIndex; $i++) {
            $column = array_column($this->ticTacToeBoard, $i);

            $rowSum = 0;

            foreach ($this->ticTacToeBoard[$i] as $item) {
                if ($item == $playerUnit) $rowSum++;

                if ($rowSum == 1 && empty($this->nextMove)) {
                    $coordinates = $this->findEmptyItemInRow($this->ticTacToeBoard, $i);
                    if (!empty($coordinates)) $this->nextMove = [$coordinates[1], $coordinates[0], $player];
                }
            }

            $columnSum = 0;

            foreach ($column as $item) {
                if ($item == $playerUnit) $columnSum++;

                if ($columnSum == 1 && empty($this->nextMove)) {
                    $coordinates = $this->findEmptyItemInColumn($this->ticTacToeBoard, $i);
                    if (!empty($coordinates)) $this->nextMove = [$coordinates[1], $coordinates[0], $player];
                }
            }

            if ($this->ticTacToeBoard[$i][$i] == $playerUnit) $rightDiagonalSum++;
            if ($this->ticTacToeBoard[$i][$this->maxIndex - $i] == $playerUnit) $leftDiagonalSum++;

            if ($rightDiagonalSum == 1 && empty($this->nextMove)) {
                $coordinates = $this->findEmptyItemInRightDiagonal($this->ticTacToeBoard);
                if (!empty($coordinates)) $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }

            if ($leftDiagonalSum == 1 && empty($this->nextMove)) {
                $coordinates = $this->findEmptyItemInLeftDiagonal($this->ticTacToeBoard);
                if (!empty($coordinates)) $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }

            for ($j = 0; $j <= $this->maxIndex; $j++) {
                if ($block && $this->ticTacToeBoard[$i][$j] == $playerUnit) {
                    $coordinates = $this->getFirstEmptyAdjscentElement($i, $j);
                    if (!empty($coordinates)) $this->nextMove = [$coordinates[1], $coordinates[0], $player];
                }
            }
        }

        if (empty($this->nextMove) && !$block) {
            $this->nextMove($playerUnit, true);
        }
        return false;
    }

    /**
     * Check winning diagonal, also return if required next empty diagonal value
     *
     * @param $playerUnit
     * @param string $type
     * @param bool $returnCount
     * @return array|bool
     */
    private function checkWiningDiagonal($playerUnit, $type = 'right', $returnCount = false)
    {
        $keys = ($type == 'right') ? array_keys($this->ticTacToeBoard) : array_reverse(array_keys($this->ticTacToeBoard));

        $diagonal = array_map(function ($row, $index) {
            return $row[$index];
        }, $this->ticTacToeBoard, $keys);

        $a = array_filter($diagonal, function ($val) use ($playerUnit) {
            return ($val === $playerUnit);
        });

        //return diagonal values
        if ($returnCount === true) {
            return $a;
        }
        return (count($a) == $this->boardSize) ? true : false;
    }

    /**
     * Check Winning row and column, also return if require next empty index from row or column
     *
     * @param $playerUnit
     * @param bool $row
     * @param bool $checkRowColumn
     * @return array|bool
     */
    private function checkWiningRowColumn($playerUnit, $row = false, $checkRowColumn = false)
    {
        /**
         * check row/column winning for player unit
         */
        for ($i = 0; $i <= $this->maxIndex; $i++) {
            //get array of row/column
            $array = ($row === true) ? $this->ticTacToeBoard[$i] : array_column($this->ticTacToeBoard, $i);

            $row_column = array_filter($array, function ($val) use ($playerUnit) {
                return ($val === $playerUnit);
            });

            if ($checkRowColumn === true && count($row_column) == $this->maxIndex) {
                return [true, $i];
            }
            if (count($row_column) == $this->boardSize) {
                return true;
            }
        }
    }

    /**
     * Defines next move if diagonals, columns and rows have count two
     * of the player unit or the opponent.
     *
     * If block set to true, counts diagonals, rows and columns for the opponent.
     * Checks also if the player or the opponent won, sets the message.
     *
     * @param string $playerUnit
     * @param bool $block
     * @return bool
     */
    public function checkWinning($playerUnit = 'X', $block = false)
    {
        $player = $playerUnit;

        if ($block) {
            if ($playerUnit == 'X') {
                $playerUnit = 'O';
            } else {
                $playerUnit = 'X';
            }
        }

        if ($this->checkWiningDiagonal($playerUnit, 'right') || $this->checkWiningDiagonal($playerUnit, 'left') || $this->checkWiningRowColumn($playerUnit, false) || $this->checkWiningRowColumn($playerUnit, true)) {
            $this->setMessage($playerUnit . " wins!");
            return true;
        }

        $rightDiagonal = $this->checkWiningDiagonal($playerUnit, 'right', true);
        if (count($rightDiagonal) == $this->maxIndex) {
            $coordinates = $this->findEmptyItemInRightDiagonal($this->ticTacToeBoard);
            if (!empty($coordinates) && empty($this->nextMove)) {
                $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }
        }

        $leftDiagonal = $this->checkWiningDiagonal($playerUnit, 'left', true);
        if (count($leftDiagonal) == $this->maxIndex) {
            $coordinates = $this->findEmptyItemInLeftDiagonal($this->ticTacToeBoard);
            if (!empty($coordinates) && empty($this->nextMove)) {
                $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }
        }

        $playerUnitColumn = $this->checkWiningRowColumn($playerUnit, false, true);
        if ($playerUnitColumn && $playerUnitColumn[0] == true) {
            $coordinates = $this->findEmptyItemInColumn($this->ticTacToeBoard, $playerUnitColumn[1]);
            if (!empty($coordinates) && empty($this->nextMove)) {
                $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }
        }

        $playerUnitRow = $this->checkWiningRowColumn($playerUnit, true, true);
        if ($playerUnitRow && $playerUnitRow[0] == true) {
            $coordinates = $this->findEmptyItemInRow($this->ticTacToeBoard,$playerUnitRow[1]);
            if (!empty($coordinates) && empty($this->nextMove)) {
                $this->nextMove = [$coordinates[1], $coordinates[0], $player];
            }
        }

        if (empty($this->nextMove) && !$block) {
            $this->checkWinning($playerUnit, true);
            if (empty($this->nextMove)) {
                $this->nextMove($playerUnit);
            }
        }

        if ($this->message == "") $this->setMessage($player . " played.");
        return false;
    }

    /**
     * Gives neighboor keys of a given coordinate in an NxN array
     *
     * @param int $size
     * @param int $y
     * @param int $x
     * @return array
     */
    function findNeighboorKeys($size, $y, $x)
    {
        $result = [];
        if ($y - 1 >= 0) {
            array_push($result, [$y - 1, $x]);
            if ($x == $y) array_push($result, [$y - 1, $x - 1]);
        }

        if ($y + 1 < $size) {
            array_push($result, [$y + 1, $x]);
            if ($x == $y) array_push($result, [$y + 1, $x + 1]);
        }
        if ($x - 1 >= 0) array_push($result, [$y, $x - 1]);
        if ($x + 1 < $size) array_push($result, [$y, $x + 1]);
        return $result;
    }

    /**
     * Retrieves first empty coordinates in a given column.
     *
     * @param array $board
     * @param int $x
     * @return array
     */
    function findEmptyItemInColumn(array $board, $x)
    {
        $column = array_column($board, $x);

        $a = array_filter($column, function ($val) {
            return ($val === "");
        });
        return ($a) ? [key($a), $x] : [];
    }

    /**
     * Retrieves first empty coordinates in a given Row.
     *
     * @param array $board
     * @param int $y
     * @return array
     */
    function findEmptyItemInRow(array $board, $y)
    {
        $a = array_filter($board[$y], function ($val) {
            return ($val === "");
        });
        return ($a) ? [$y, key($a)] : [];
    }

    /**
     * Retrieves first empty coordinates in left diagonal of an NxN array.
     *
     * @param array $board
     * @return array
     */
    private function findEmptyItemInLeftDiagonal(array $board)
    {
        $leftDiag = array_map(function ($row, $index) {
            return $row[$index];
        }, $board, array_reverse(array_keys($board)));


        $a = array_filter($leftDiag, function ($val) {
            return ($val === "");
        });

        return ($a) ? [key($a), sizeof($board) - key($a) - 1] : [];
    }

    /**
     * Retrieves first empty coordinates in right diagonal of an NxN array.
     *
     * @param array $board
     * @return array
     */
    private function findEmptyItemInRightDiagonal(array $board)
    {
        $rightDiag = array_map(function ($row, $index) {
            return $row[$index];
        }, $board, array_keys($board));

        $a = array_filter($rightDiag, function ($val) {
            return ($val === "");
        });
        return ($a) ? [key($a), key($a)] : [];
    }
}