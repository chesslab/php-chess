The `Chess\Ascii` class provides a number of methods that can be used to convert `Chess\Board` objects into character-based representations such as strings or arrays, and vice versa.

#### `toArray(Board $board, bool $flip = false): array`

Returns an ASCII array from a `Chess\Board` object.

```php
use Chess\Ascii;
use Chess\Board;

$board = new Board();
$board->play('w', 'e4');
$board->play('b', 'e5');

$array = (new Ascii())->toArray($board);

print_r($array);
```

Output:

```
Array
(
    [7] => Array
        (
            [0] =>  r
            [1] =>  n
            [2] =>  b
            [3] =>  q
            [4] =>  k
            [5] =>  b
            [6] =>  n
            [7] =>  r
        )

    [6] => Array
        (
            [0] =>  p
            [1] =>  p
            [2] =>  p
            [3] =>  p
            [4] =>  .
            [5] =>  p
            [6] =>  p
            [7] =>  p
        )

    [5] => Array
        (
            [0] =>  .
            [1] =>  .
            [2] =>  .
            [3] =>  .
            [4] =>  .
            [5] =>  .
            [6] =>  .
            [7] =>  .
        )

    [4] => Array
        (
            [0] =>  .
            [1] =>  .
            [2] =>  .
            [3] =>  .
            [4] =>  p
            [5] =>  .
            [6] =>  .
            [7] =>  .
        )

    [3] => Array
        (
            [0] =>  .
            [1] =>  .
            [2] =>  .
            [3] =>  .
            [4] =>  P
            [5] =>  .
            [6] =>  .
            [7] =>  .
        )

    [2] => Array
        (
            [0] =>  .
            [1] =>  .
            [2] =>  .
            [3] =>  .
            [4] =>  .
            [5] =>  .
            [6] =>  .
            [7] =>  .
        )

    [1] => Array
        (
            [0] =>  P
            [1] =>  P
            [2] =>  P
            [3] =>  P
            [4] =>  .
            [5] =>  P
            [6] =>  P
            [7] =>  P
        )

    [0] => Array
        (
            [0] =>  R
            [1] =>  N
            [2] =>  B
            [3] =>  Q
            [4] =>  K
            [5] =>  B
            [6] =>  N
            [7] =>  R
        )

)
```

#### `toBoard(array $array, string $turn, $castle = null): Board`

Returns a `Chess\Board` object from an ASCII array.

```php
use Chess\Ascii;

$sq = [
    7 => [ ' r ', ' n ', ' . ', ' q ', ' k ', ' b ', ' . ', ' r ' ],
    6 => [ ' . ', ' . ', ' . ', ' . ', ' p ', ' p ', ' . ', ' p ' ],
    5 => [ ' . ', ' . ', ' . ', ' p ', ' . ', ' n ', ' p ', ' . ' ],
    4 => [ ' . ', ' . ', ' p ', ' P ', ' . ', ' . ', ' . ', ' . ' ],
    3 => [ ' . ', ' . ', ' . ', ' . ', ' P ', ' . ', ' . ', ' . ' ],
    2 => [ ' . ', ' . ', ' N ', ' . ', ' . ', ' . ', ' P ', ' . ' ],
    1 => [ ' P ', ' P ', ' . ', ' . ', ' . ', ' P ', ' . ', ' P ' ],
    0 => [ ' R ', ' . ', ' B ', ' Q ', ' . ', ' K ', ' N ', ' R ' ],
];

$castle = [
    'w' => [
        'isCastled' => false,
        'O-O' => false,
        'O-O-O' => false,
    ],
    'b' => [
        'isCastled' => false,
        'O-O' => true,
        'O-O-O' => true,
    ],
];

$board = (new Ascii())->toBoard($sq, 'b', $castle);
```

#### `print(Board $board): string`

Returns an ASCII string from a `Chess\Board` object.

```php
use Chess\Ascii;
use Chess\Board;

$board = new Board();
$board->play('w', 'e4');
$board->play('b', 'e5');

$string = (new Ascii())->print($board);

print_r($string);
```

Output:

```
r  n  b  q  k  b  n  r
p  p  p  p  .  p  p  p
.  .  .  .  .  .  .  .
.  .  .  .  p  .  .  .
.  .  .  .  P  .  .  .
.  .  .  .  .  .  .  .
P  P  P  P  .  P  P  P
R  N  B  Q  K  B  N  R
```

#### `fromAlgebraicToIndex(string $sq): array`

Returns the ASCII array indexes of a square described in algebraic notation.

```php
use Chess\Ascii;

$array = (new Ascii())->fromAlgebraicToIndex('a1');

print_r($array);
```

Output:

```
Array
(
    [0] => 0
    [1] => 0
)
```

#### `fromIndexToAlgebraic(int $i, int $j): string`

Returns the square in algebraic notation corresponding to the given ASCII array indexes.

```php
use Chess\Ascii;

$string = (new Ascii())->fromIndexToAlgebraic(7, 7);

print_r($string);
```

Output:

```
h8
```

#### `setArrayElem(string $piece, string $sq, &$array): Ascii`

Sets a piece in a specific square in the given ASCII array.

```php
use Chess\Ascii;

$sq = [
    7 => [ ' r ', ' n ', ' b ', ' q ', ' k ', ' b ', ' n ', ' r ' ],
    6 => [ ' p ', ' p ', ' p ', ' p ', ' . ', ' p ', ' p ', ' p ' ],
    5 => [ ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ' ],
    4 => [ ' . ', ' . ', ' . ', ' . ', ' p ', ' . ', ' . ', ' . ' ],
    3 => [ ' . ', ' . ', ' . ', ' . ', ' P ', ' . ', ' . ', ' . ' ],
    2 => [ ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ' ],
    1 => [ ' P ', ' P ', ' P ', ' P ', ' . ', ' P ', ' P ', ' P ' ],
    0 => [ ' R ', ' N ', ' B ', ' Q ', ' K ', ' B ', ' N ', ' R ' ],
];

$ascii = new Ascii();

$board = $ascii->setArrayElem(' . ', 'g1', $sq)
            ->setArrayElem(' N ', 'f3', $sq)
            ->toBoard($sq, 'b');

$board->play('b', 'Nc6');

$string = (new Ascii())->print($board);

print_r($string);
```

Output:

```
r  .  b  q  k  b  n  r
p  p  p  p  .  p  p  p
.  .  n  .  .  .  .  .
.  .  .  .  p  .  .  .
.  .  .  .  P  .  .  .
.  .  .  .  .  N  .  .
P  P  P  P  .  P  P  P
R  N  B  Q  K  B  .  R
```
