<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic-tac-toe</title>
    <link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet">
    
    <link href="newCascadeStyleSheet.css" rel="stylesheet" type="text/css"/>
  </head>
  <body onload="initialize()">
    <h1>Tic-Tac-Toe</h1>
    <table id="table_game">
      <tr><td class="td_game"><div id="cell0" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell1" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell2" onclick="cellClicked(this.id)" class="fixed"></div></td></tr>
      <tr><td class="td_game"><div id="cell3" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell4" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell5" onclick="cellClicked(this.id)" class="fixed"></div></td></tr>
      <tr><td class="td_game"><div id="cell6" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell7" onclick="cellClicked(this.id)" class="fixed"></div></td><td class="td_game"><div id="cell8" onclick="cellClicked(this.id)" class="fixed"></div></td></tr>
    </table>
    <!--    for the user input for accessibility-->
    <div id="userInput">
    <label for="cellNumber">Enter a number (1-9):</label>
    <input type="number" id="cellNumber" min="1" max="9">
    <button onclick="selectBoxByNumber()">Select</button>
    </div>
    
    <div id="restart" title="Start new game" onclick="restartGame(true)"><span style="vertical-align:top;position:relative;top:-10px">#</span></div>
    <table>
      <tr><th class="th_list">Player 1</th><th class="th_list" style="padding-right:10px;padding-left:10px">Player 2</th><th class="th_list">Draws</th></tr>
      <tr><td class="td_list" id="player_score">0</td><td class="td_list" id="computer_score">0</td><td class="td_list" style="padding-right:10px;padding-left:10px" id="tie_score">0</td></tr>
    </table>
    <!-- The modal dialog for announcing the winner -->
    <div id="winAnnounce" class="modal">
       Modal content 
      <div class="modal-content">
        <span class="close" onclick="closeModal('winAnnounce')">&times;</span>
        <p id="winText"></p>
      </div>
    </div>
    <!-- The dialog for getting feedback from the user -->
    <div id="userFeedback" class="modal">
      <!-- Modal content -->
      <div class="modal-content">
        <p id="questionText"></p>
        <p><button id="yesBtn">Yes</button>&nbsp;<button id="noBtn">No</button></p>
      </div>
    </div>
    <!-- The options dialog -->
    <div id="optionsDlg" class="modal">
      <!-- Modal content -->
      <div class="modal-content">
        <h2>How would you like to play?</h2>
          <h3>Difficulty:</h3>
          <label><input type="radio" name="difficulty" id="r0" value="0">easy&nbsp;</label>
          <label><input type="radio" name="difficulty" id="r1" value="1" checked>hard</label><br>
          <h3>Play as:</h3>
          <label><input type="radio" name="player" id="rx" value="x" checked>X (go first)&nbsp;</label>
          <label><input type="radio" name="player" id="ro" value="o">O<br></label>
          <p><button id="okBtn" onclick="getOptions()">Play</button></p>
      </div>
    </div>
    

    
  </body>
</html>
<script>
    "use strict";

/*

A SIMPLE TIC-TAC-TOE GAME IN JAVASCRIPT

(1) Grid layout

The game grid is represented in the array Grid.cells as follows:

[0] [1] [2]
[3] [4] [5]
[6] [7] [8]

The cells (array elements) hold the following numeric values:
0 if not occupied, 1 for player, 3 for computer.
This allows us to quickly get an overview of the game state:
if the sum of all the cells in a row is 9, the computer wins,
if it is 3 and all the cells are occupied, the human player wins,
etc.

(2) Strategy of makeComputerMove()

The computer first  looks for almost completed rows, columns, and
diagonals, where there are two fields occupied either by the human
player or by the computer itself. If the computer can win by
completing a sequence, it does so; if it can block the player from
winning with the next move, it does that. If none of that applies,
it plays the center field if that's free, otherwise it selects a
random free field. This is not a 100 % certain strategy, but the
gameplay experience is fairly decent.

*/

//==================================
// EVENT BINDINGS
//==================================

// Bind Esc key to closing the modal dialog
document.onkeypress = function (evt) {
    evt = evt || window.event;
    var modal = document.getElementsByClassName("modal")[0];
    if (evt.keyCode === 27) {
        modal.style.display = "none";
    }
};

// When the user clicks anywhere outside of the modal dialog, close it
window.onclick = function (evt) {
    var modal = document.getElementsByClassName("modal")[0];
    if (evt.target === modal) {
        modal.style.display = "none";
    }
};

//==================================
// HELPER FUNCTIONS
//==================================
function sumArray(array) {
    var sum = 0,
        i = 0;
    for (i = 0; i < array.length; i++) {
        sum += array[i];
    }
    return sum;
}

function isInArray(element, array) {
    if (array.indexOf(element) > -1) {
        return true;
    }
    return false;
}

function shuffleArray(array) {
    var counter = array.length,
        temp,
        index;
    while (counter > 0) {
        index = Math.floor(Math.random() * counter);
        counter--;
        temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }
    return array;
}

function intRandom(min, max) {
    var rand = min + Math.random() * (max + 1 - min);
    return Math.floor(rand);
}

// GLOBAL VARIABLES
var moves = 0,
    winner = 0,
    x = 1,
    o = 3,
    player1 = x,
    player2 = o,
    whoseTurn = x,
    gameOver = false,
    score = {
        ties: 0,
        player: 0,
        computer: 0
    },
    xText = "<span class=\"x\">&times;</class>",
    oText = "<span class=\"o\">o</class>",
    playerText = xText,
    computerText = oText,
    difficulty = 1,
    myGrid = null;

//==================================
// GRID OBJECT
//==================================

// Grid constructor
//=================
function Grid() {
    this.cells = new Array(9);
}

// Grid methods
//=============

// Get free cells in an array.
// Returns an array of indices in the original Grid.cells array, not the values
// of the array elements.
// Their values can be accessed as Grid.cells[index].
Grid.prototype.getFreeCellIndices = function () {
    var i = 0,
        resultArray = [];
    for (i = 0; i < this.cells.length; i++) {
        if (this.cells[i] === 0) {
            resultArray.push(i);
        }
    }
    // console.log("resultArray: " + resultArray.toString());
    // debugger;
    return resultArray;
};

// Get a row (accepts 0, 1, or 2 as argument).
// Returns the values of the elements.
Grid.prototype.getRowValues = function (index) {
    if (index !== 0 && index !== 1 && index !== 2) {
        console.error("Wrong arg for getRowValues!");
        return undefined;
    }
    var i = index * 3;
    return this.cells.slice(i, i + 3);
};

// Get a row (accepts 0, 1, or 2 as argument).
// Returns an array with the indices, not their values.
Grid.prototype.getRowIndices = function (index) {
    if (index !== 0 && index !== 1 && index !== 2) {
        console.error("Wrong arg for getRowIndices!");
        return undefined;
    }
    var row = [];
    index = index * 3;
    row.push(index);
    row.push(index + 1);
    row.push(index + 2);
    return row;
};

// get a column (values)
Grid.prototype.getColumnValues = function (index) {
    if (index !== 0 && index !== 1 && index !== 2) {
        console.error("Wrong arg for getColumnValues!");
        return undefined;
    }
    var i, column = [];
    for (i = index; i < this.cells.length; i += 3) {
        column.push(this.cells[i]);
    }
    return column;
};

// get a column (indices)
Grid.prototype.getColumnIndices = function (index) {
    if (index !== 0 && index !== 1 && index !== 2) {
        console.error("Wrong arg for getColumnIndices!");
        return undefined;
    }
    var i, column = [];
    for (i = index; i < this.cells.length; i += 3) {
        column.push(i);
    }
    return column;
};

// get diagonal cells
// arg 0: from top-left
// arg 1: from top-right
Grid.prototype.getDiagValues = function (arg) {
    var cells = [];
    if (arg !== 1 && arg !== 0) {
        console.error("Wrong arg for getDiagValues!");
        return undefined;
    } else if (arg === 0) {
        cells.push(this.cells[0]);
        cells.push(this.cells[4]);
        cells.push(this.cells[8]);
    } else {
        cells.push(this.cells[2]);
        cells.push(this.cells[4]);
        cells.push(this.cells[6]);
    }
    return cells;
};

// get diagonal cells
// arg 0: from top-left
// arg 1: from top-right
Grid.prototype.getDiagIndices = function (arg) {
    if (arg !== 1 && arg !== 0) {
        console.error("Wrong arg for getDiagIndices!");
        return undefined;
    } else if (arg === 0) {
        return [0, 4, 8];
    } else {
        return [2, 4, 6];
    }
};

// Get first index with two in a row (accepts computer or player as argument)
Grid.prototype.getFirstWithTwoInARow = function (agent) {
    if (agent !== player2 && agent !== player1) {
        console.error("Function getFirstWithTwoInARow accepts only player or computer as argument.");
        return undefined;
    }
    var sum = agent * 2,
        freeCells = shuffleArray(this.getFreeCellIndices());
    for (var i = 0; i < freeCells.length; i++) {
        for (var j = 0; j < 3; j++) {
            var rowV = this.getRowValues(j);
            var rowI = this.getRowIndices(j);
            var colV = this.getColumnValues(j);
            var colI = this.getColumnIndices(j);
            if (sumArray(rowV) == sum && isInArray(freeCells[i], rowI)) {
                return freeCells[i];
            } else if (sumArray(colV) == sum && isInArray(freeCells[i], colI)) {
                return freeCells[i];
            }
        }
        for (j = 0; j < 2; j++) {
            var diagV = this.getDiagValues(j);
            var diagI = this.getDiagIndices(j);
            if (sumArray(diagV) == sum && isInArray(freeCells[i], diagI)) {
                return freeCells[i];
            }
        }
    }
    return false;
};

Grid.prototype.reset = function () {
    for (var i = 0; i < this.cells.length; i++) {
        this.cells[i] = 0;
    }
    return true;
};

//==================================
// MAIN FUNCTIONS
//==================================

// executed when the page loads
function initialize() {
    myGrid = new Grid();
    moves = 0;
    winner = 0;
    gameOver = false;
    whoseTurn = player1; // default, this may change
    for (var i = 0; i <= myGrid.cells.length - 1; i++) {
        myGrid.cells[i] = 0;
    }
    // setTimeout(assignRoles, 500);
    setTimeout(showOptions, 500);
    // debugger;
}

// Ask player if they want to play as X or O. X goes first.
function assignRoles() {
    askUser("Do you want to go first?");
    document.getElementById("yesBtn").addEventListener("click", makePlayerX);
    document.getElementById("noBtn").addEventListener("click", makePlayerO);
}

function makePlayerX() {
    player1 = x;
    player2 = o;
    whoseTurn = player1;
    playerText = xText;
    computerText = oText;
    document.getElementById("userFeedback").style.display = "none";
    document.getElementById("yesBtn").removeEventListener("click", makePlayerX);
    document.getElementById("noBtn").removeEventListener("click", makePlayerO);
}

function makePlayerO() {
    player1 = o;
    player2 = x;
    whoseTurn = player2;
    playerText = oText;
    computerText = xText;
//    setTimeout(makeComputerMove, 400);
    document.getElementById("userFeedback").style.display = "none";
    document.getElementById("yesBtn").removeEventListener("click", makePlayerX);
    document.getElementById("noBtn").removeEventListener("click", makePlayerO);
}

// executed when player clicks one of the table cells
function cellClicked(id) {
    // The last character of the id corresponds to the numeric index in Grid.cells:
    var idName = id.toString();
    var cell = parseInt(idName[idName.length - 1]);
    if (myGrid.cells[cell] > 0 || gameOver) {
        // cell is already occupied or something else is wrong
        return false;
    }
    moves += 1;
    if(whoseTurn===player1){
        document.getElementById(id).innerHTML = playerText;
    }
    else{
        document.getElementById(id).innerHTML = computerText;
    }
    // randomize orientation (for looks only)
    var rand = Math.random();
    if (rand < 0.3) {
        document.getElementById(id).style.transform = "rotate(180deg)";
    } else if (rand > 0.6) {
        document.getElementById(id).style.transform = "rotate(90deg)";
    }
    document.getElementById(id).style.cursor = "default";
    myGrid.cells[cell] = whoseTurn;
    // Test if we have a winner:
    if (moves >= 5) {
        winner = checkWin();
    }
    if (winner === 0) {
       if(whoseTurn === player1){
           whoseTurn=player2;
       }
       else{
           whoseTurn=player1;
       }
//        makeComputerMove();
    }
    return true;
}

// Executed when player hits restart button.
// ask should be true if we should ask users if they want to play as X or O
function restartGame(ask) {
    if (moves > 0) {
        var response = confirm("Are you sure you want to start over?");
        if (response === false) {
            return;
        }
    }
    gameOver = false;
    moves = 0;
    winner = 0;
    whoseTurn = x;
    myGrid.reset();
    for (var i = 0; i <= 8; i++) {
        var id = "cell" + i.toString();
        document.getElementById(id).innerHTML = "";
        document.getElementById(id).style.cursor = "pointer";
        document.getElementById(id).classList.remove("win-color");
    }
    if (ask === true) {
        // setTimeout(assignRoles, 200);
        setTimeout(showOptions, 200);
    } else if (whoseTurn == player2) {
        setTimeout(makeComputerMove, 800);
    }
}

// The core logic of the game AI:
//function makeComputerMove() {
//    // debugger;
//    if (gameOver) {
//        return false;
//    }
//    var cell = -1,
//        myArr = [],
//        corners = [0,2,6,8];
//    if (moves >= 3) {
//        cell = myGrid.getFirstWithTwoInARow(computer);
//        if (cell === false) {
//            cell = myGrid.getFirstWithTwoInARow(player);
//        }
//        if (cell === false) {
//            if (myGrid.cells[4] === 0 && difficulty == 1) {
//                cell = 4;
//            } else {
//                myArr = myGrid.getFreeCellIndices();
//                cell = myArr[intRandom(0, myArr.length - 1)];
//            }
//        }
//        // Avoid a catch-22 situation:
//        if (moves == 3 && myGrid.cells[4] == computer && player == x && difficulty == 1) {
//            if (myGrid.cells[7] == player && (myGrid.cells[0] == player || myGrid.cells[2] == player)) {
//                myArr = [6,8];
//                cell = myArr[intRandom(0,1)];
//            }
//            else if (myGrid.cells[5] == player && (myGrid.cells[0] == player || myGrid.cells[6] == player)) {
//                myArr = [2,8];
//                cell = myArr[intRandom(0,1)];
//            }
//            else if (myGrid.cells[3] == player && (myGrid.cells[2] == player || myGrid.cells[8] == player)) {
//                myArr = [0,6];
//                cell = myArr[intRandom(0,1)];
//            }
//            else if (myGrid.cells[1] == player && (myGrid.cells[6] == player || myGrid.cells[8] == player)) {
//                myArr = [0,2];
//                cell = myArr[intRandom(0,1)];
//            }
//        }
//        else if (moves == 3 && myGrid.cells[4] == player && player == x && difficulty == 1) {
//            if (myGrid.cells[2] == player && myGrid.cells[6] == computer) {
//                cell = 8;
//            }
//            else if (myGrid.cells[0] == player && myGrid.cells[8] == computer) {
//                cell = 6;
//            }
//            else if (myGrid.cells[8] == player && myGrid.cells[0] == computer) {
//                cell = 2;
//            }
//            else if (myGrid.cells[6] == player && myGrid.cells[2] == computer) {
//                cell = 0;
//            }
//        }
//    } else if (moves === 1 && myGrid.cells[4] == player && difficulty == 1) {
//        // if player is X and played center, play one of the corners
//        cell = corners[intRandom(0,3)];
//    } else if (moves === 2 && myGrid.cells[4] == player && computer == x && difficulty == 1) {
//        // if player is O and played center, take two opposite corners
//        if (myGrid.cells[0] == computer) {
//            cell = 8;
//        }
//        else if (myGrid.cells[2] == computer) {
//            cell = 6;
//        }
//        else if (myGrid.cells[6] == computer) {
//            cell = 2;
//        }
//        else if (myGrid.cells[8] == computer) {
//            cell = 0;
//        }
//    } else if (moves === 0 && intRandom(1,10) < 8) {
//        // if computer is X, start with one of the corners sometimes
//        cell = corners[intRandom(0,3)];
//    } else {
//        // choose the center of the board if possible
//        if (myGrid.cells[4] === 0 && difficulty == 1) {
//            cell = 4;
//        } else {
//            myArr = myGrid.getFreeCellIndices();
//            cell = myArr[intRandom(0, myArr.length - 1)];
//        }
//    }
//    var id = "cell" + cell.toString();
//    // console.log("computer chooses " + id);
//    document.getElementById(id).innerHTML = computerText;
//    document.getElementById(id).style.cursor = "default";
//    // randomize rotation of marks on the board to make them look
//    // as if they were handwritten
//    var rand = Math.random();
//    if (rand < 0.3) {
//        document.getElementById(id).style.transform = "rotate(180deg)";
//    } else if (rand > 0.6) {
//        document.getElementById(id).style.transform = "rotate(90deg)";
//    }
//    myGrid.cells[cell] = computer;
//    moves += 1;
//    if (moves >= 5) {
//        winner = checkWin();
//    }
//    if (winner === 0 && !gameOver) {
//        whoseTurn = player;
//    }
//}

// Check if the game is over and determine winner
function checkWin() {
    winner = 0;

    // rows
    for (var i = 0; i <= 2; i++) {
        var row = myGrid.getRowValues(i);
        if (row[0] > 0 && row[0] == row[1] && row[0] == row[2]) {
            if (row[0] == player2) {
                score.computer++;
                winner = player2;
                // console.log("computer wins");
            } else {
                score.player++;
                winner = player1;
                // console.log("player wins");
            }
            // Give the winning row/column/diagonal a different bg-color
            var tmpAr = myGrid.getRowIndices(i);
            for (var j = 0; j < tmpAr.length; j++) {
                var str = "cell" + tmpAr[j];
                document.getElementById(str).classList.add("win-color");
            }
            setTimeout(endGame, 1000, winner);
            return winner;
        }
    }

    // columns
    for (i = 0; i <= 2; i++) {
        var col = myGrid.getColumnValues(i);
        if (col[0] > 0 && col[0] == col[1] && col[0] == col[2]) {
            if (col[0] == player2) {
                score.computer++;
                winner = player2;
                // console.log("computer wins");
            } else {
                score.player++;
                winner = player1;
                // console.log("player wins");
            }
            // Give the winning row/column/diagonal a different bg-color
            var tmpAr = myGrid.getColumnIndices(i);
            for (var j = 0; j < tmpAr.length; j++) {
                var str = "cell" + tmpAr[j];
                document.getElementById(str).classList.add("win-color");
            }
            setTimeout(endGame, 1000, winner);
            return winner;
        }
    }

    // diagonals
    for (i = 0; i <= 1; i++) {
        var diagonal = myGrid.getDiagValues(i);
        if (diagonal[0] > 0 && diagonal[0] == diagonal[1] && diagonal[0] == diagonal[2]) {
            if (diagonal[0] == player2) {
                score.computer++;
                winner = player2;
                // console.log("computer wins");
            } else {
                score.player++;
                winner = player1;
                // console.log("player wins");
            }
            // Give the winning row/column/diagonal a different bg-color
            var tmpAr = myGrid.getDiagIndices(i);
            for (var j = 0; j < tmpAr.length; j++) {
                var str = "cell" + tmpAr[j];
                document.getElementById(str).classList.add("win-color");
            }
            setTimeout(endGame, 1000, winner);
            return winner;
        }
    }

    // If we haven't returned a winner by now, if the board is full, it's a tie
    var myArr = myGrid.getFreeCellIndices();
    if (myArr.length === 0) {
        winner = 10;
        score.ties++;
        endGame(winner);
        return winner;
    }
    

    return winner;
}

function announceWinner(text) {
    document.getElementById("winText").innerHTML = text;
    document.getElementById("winAnnounce").style.display = "block";
    setTimeout(closeModal, 1400, "winAnnounce");
}

function askUser(text) {
    document.getElementById("questionText").innerHTML = text;
    document.getElementById("userFeedback").style.display = "block";
}

function showOptions() {
    if (player1 == o) {
        document.getElementById("rx").checked = false;
        document.getElementById("ro").checked = true;
    }
    else if (player1 == x) {
        document.getElementById("rx").checked = true;
        document.getElementById("ro").checked = false;
    }
    if (difficulty === 0) {
        document.getElementById("r0").checked = true;
        document.getElementById("r1").checked = false;
    }
    else {
        document.getElementById("r0").checked = false;
        document.getElementById("r1").checked = true;
    }
    document.getElementById("optionsDlg").style.display = "block";
}

function getOptions() {
    var diffs = document.getElementsByName('difficulty');
    for (var i = 0; i < diffs.length; i++) {
        if (diffs[i].checked) {
            difficulty = parseInt(diffs[i].value);
            break;
            // debugger;
        }
    }
    if (document.getElementById('rx').checked === true) {
        player1 = x;
        player2 = o;
        whoseTurn = player1;
        playerText = xText;
        computerText = oText;
    }
    else {
        player1 = o;
        player2 = x;
        whoseTurn = player2;
        playerText = oText;
        computerText = xText;
        setTimeout(makeComputerMove, 400);
    }
    document.getElementById("optionsDlg").style.display = "none";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}

function endGame(who) {
    if (who == player1) {
        announceWinner("Congratulations, Player 1 Wins!");
    } else if (who == player2) {
        announceWinner("Congratulations, Player 2 Wins!");
    } else {
        announceWinner("It's a tie!");
    }
    gameOver = true;
    whoseTurn = 0;
    moves = 0;
    winner = 0;
    document.getElementById("computer_score").innerHTML = score.computer;
    document.getElementById("tie_score").innerHTML = score.ties;
    document.getElementById("player_score").innerHTML = score.player;
    for (var i = 0; i <= 8; i++) {
        var id = "cell" + i.toString();
        document.getElementById(id).style.cursor = "default";
    }
    setTimeout(restartGame, 800);
}
function selectBoxByNumber() {
    var cellNumberInput = document.getElementById("cellNumber");
    var cellNumber = parseInt(cellNumberInput.value);

    // Check if the input is within the valid range (1-9)
    if (cellNumber >= 1 && cellNumber <= 9) {
        // Calculate the cell index (0-8) from the input number
        var cellIndex = cellNumber - 1;

        // Get the ID of the corresponding cell div
        var cellId = "cell" + cellIndex;

        // Call the cellClicked function to simulate a click on the selected cell
        cellClicked(cellId);

        // Clear the input field
        cellNumberInput.value = "";
    } else {
        // Display an error message if the input is out of range
        alert("Please enter a number between 1 and 9.");
    }
}

</script>
