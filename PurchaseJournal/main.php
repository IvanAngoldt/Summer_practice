<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="styles/style.css">
    <link type="image/x-icon" href="https://pngimg.com/uploads/ring/small/ring_PNG98.png" rel="shortcut icon">
    <link type="Image/x-icon" href="https://pngimg.com/uploads/ring/small/ring_PNG98.png" rel="icon">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <title>Jewelry store</title>
    <script>
        $( function() {
            var range1 = <?php echo empty($range1)?"0":$range1?>;
            var range2 = <?php echo empty($range2)?"10000":$range2?>;

            $( "#slider-range" ).slider({
                range: true,
                min: 0,
                max: 10000,
                values: [ range1, range2 ],
                slide: function( event, ui ) {
                    $( "#amount" ).val(ui.values[ 0 ] + " ₽ - " + ui.values[ 1 ] + " ₽" );
                }
            });
            $( "#amount" ).val($( "#slider-range" ).slider( "values", 0 ) + " ₽ - " + $( "#slider-range" ).slider( "values", 1 ) + " ₽" );
        });

        function toggleFilter() {
            var filterBlock = document.getElementById("filter-block");
            if (filterBlock.style.display === "none") {
                filterBlock.style.display = "block";
            } else {
                filterBlock.style.display = "none";
            }
        }

        var expanded = false;
        function showCheckboxes() {
            var checkboxes = document.getElementById("checkboxes1");
            if (!expanded) {
                checkboxes.style.display = "block";
                expanded = true;
            } else {
                checkboxes.style.display = "none";
                expanded = false;
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="header-items">
            <a href="index.php" class="logo">
                <img src="https://pngimg.com/uploads/ring/small/ring_PNG98.png" alt="logo" width="37" height="37">
                <h1>Ювелирный магазин</h1>
            </a>
            <nav>
                <ul>
                    <li><a href="Catalog.php">Каталог изделий</a></li>
                    <li><a href="Consignors.php">Комитенты</a></li>
                    <li><a href="SalesJournal.php">Журнал сдачи</a></li>
                    <li><a class="active" href="#">Журнал покупок</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <?php
            if (!empty($_COOKIE['messages'])) {
                echo '<div class="messages">';
                $messages = unserialize($_COOKIE['messages']);
                foreach ($messages as $message) {
                    echo $message . '</br>';
                }
                echo '</div>';
                setcookie('messages', '', time() + 24 * 60 * 60);
            }
            if (!empty($_COOKIE['errors'])) {
                echo '<div class="errors">';
                $errors = unserialize($_COOKIE['errors']);
                echo '<ol>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ol></div>';
                setcookie('errors', '', time() + 24 * 60 * 60);
            }
        ?>
        <form action="" method="POST">
            <div class="main-content">
                <h2>Журнал покупок</h2>
            </div>
            <div class="main-content">
                <div class="top-table">
                    <div class="newdates">
                        <div class="newdates-item">
                            <label for="product_id">Товар</label>
                        </div>
                        <div class="newdates-item">
                            <select name="product_id">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Products");
                                $stmt->execute();
                                $Products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите товар</option>");
                                foreach ($Products as $product) {
                                    if (!empty($new['product_id']) && ($new['product_id'] ==  $product['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $product['id'], $product['id'], $product['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $product['id'], $product['id'], $product['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                            <label for="purchase_date">Дата продажи</label>
                        </div>
                        <div class="newdates-item">
                            <input type="date" name="purchase_date" value=<?php print($new['purchase_date']); ?>>
                        </div>
                        <div class="newdates-item">
                            <label for="price">Цена</label>
                        </div>
                        <div class="newdates-item">
                            <input name="price" type="number" placeholder="цена товара" min="1000" max="10000" step="500" value=<?php print($new['price']); ?>>
                        </div>
                        <div class="newdates-item">
                            <input type="submit" name="addnewdate" value="Добавить">
                        </div>
                    </div>
                    <div id="filter-block" style="display:none;">
                        <h3>Фильтр</h3>
                        <p>
                            <label for="amount">Цена:</label>
                            <input type="text" name="range" id="amount" readonly>
                        </p>
                        <div id="slider-range"></div>
                        </br>
                        <div class="row">
                            <input type="date" class="date" name="date" value="<?php echo isset($_COOKIE["date"]) ? $_COOKIE["date"] : ""?>">
                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes()">
                                    <select>
                                        <option>Товар</option>
                                    </select>
                                    <div class="overSelect"></div>
                                </div>
                                <div id="checkboxes1">
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name FROM Products");
                                    $stmt->execute();
                                    $Products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($Products as $product) {
                                        echo '<label for="product'.$product['id'].'"><input type="checkbox" ';
                                        echo empty($filter_product_ids) ? "" : (in_array($product['id'], $filter_product_ids) ? "checked " : "");
                                        echo 'name="filter_product_'.$product['id'].'" id="product'.$product['id'].'">'.$product['name'].'</label>';
                                    }
                                    ?>
                                    <button type="button" id="checkAll">Отменить всё</button>
                                </div>
                            </div>
                        </div>
                        </br></br>
                        <input type="submit" name="filter" value="Применить">
                        <input type="submit" name="resetall" value="Сбросить всё">
                    </div>
                </div>
            </div>
            <div class="main-content">
            <?php
                echo    '<table class="table-mobile">
                            <tr>
                                <th>Товар</th>
                                <th>Дата продажи</th>
                                <th>Цена</th>
                                <th colspan=2>
                                    <button type="button" onclick="toggleFilter()">
                                        <img src="https://cdn-icons-png.flaticon.com/512/107/107799.png" alt="filters" width="20" height="20">
                                    </button>
                                </th>
                            <tr>';
                foreach ($values as $value) {
                    $stmt = $db->prepare("SELECT id, name FROM Products");
                    $stmt->execute();
                    $Products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo    '<tr>';
                    echo        '<td>';
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'name="product_id'.$value['id'].'">';
                                        foreach ($Products as $product) {
                                            if ($product['id'] == $value['product_id']) {
                                                printf('<option selected value="%d">%d. %s</option>', $product['id'], $product['id'], $product['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $product['id'], $product['id'], $product['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';
                    echo        '<td> <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'type="date" name="purchase_date'.$value['id'].'" value="'.$value['purchase_date'].'"> </td>';
                    echo        '<td>
                                    <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                    else print(" "); echo 'name="price'.$value['id'].'" value="'.$value['price'].'">
                                </td>';
                if (empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) {
                    echo        '<td> <input name="edit'.$value['id'].'" type="image" src="https://static.thenounproject.com/png/2185844-200.png" width="20" height="20" alt="submit"/> </td>';
                    echo        '<td> <input name="clear'.$value['id'].'" type="image" src="https://cdn-icons-png.flaticon.com/512/860/860829.png" width="20" height="20" alt="submit"/> </td>';
                } else {
                    echo        '<td colspan=2> <input name="save'.$value['id'].'" type="image" src="https://cdn-icons-png.flaticon.com/512/84/84138.png" width="20" height="20" alt="submit"/> </td>';
                }
                    echo    '</tr>';
                }
                echo '</table>';
            ?>
            </div>
        </form>
    </main>
<script>
    document.getElementById('checkAll').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('input[type=checkbox]');
        if (this.innerHTML === 'Выбрать все') {
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
            this.innerHTML = 'Отменить все';
        } else {
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
            this.innerHTML = 'Выбрать все';
        }
    });
</script>
</body>
</html>