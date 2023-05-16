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
    <title>Jewelry store</title>
    <script>
        function toggleFilter() {
            var filterBlock = document.getElementById("filter-block");
            if (filterBlock.style.display === "none") {
                filterBlock.style.display = "block";
            } else {
                filterBlock.style.display = "none";
            }
        }

        var expanded = false;
        function showCheckboxes(checkboxesId) {
            var checkboxes = document.getElementById(checkboxesId);
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
                    <li><a class="active" href="#">Журнал сдачи</a></li>
                    <li><a href="PurchaseJournal.php">Журнал покупок</a></li>
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
                <h2>Журнал сдачи</h2>
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
                            <label for="consignor_id">Комитент</label>
                        </div>
                        <div class="newdates-item">
                            <select name="consignor_id">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Consignors");
                                $stmt->execute();
                                $Consignors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите комитента</option>");
                                foreach ($Consignors as $consignor) {
                                    if (!empty($new['consignor_id']) && ($new['consignor_id'] ==  $consignor['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $consignor['id'], $consignor['id'], $consignor['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $consignor['id'], $consignor['id'], $consignor['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                            <label for="sale_date">Дата сдачи</label>
                        </div>
                        <div class="newdates-item">
                            <input type="date" name="sale_date" value=<?php print($new['sale_date']); ?>>
                        </div>
                        <div class="newdates-item">
                            <input type="submit" name="addnewdate" value="Добавить">
                        </div>
                    </div>
                    <div id="filter-block" style="display:none;">
                        <h3>Фильтр</h3>
                        <input type="date" name="date" value="<?php echo isset($_COOKIE["date"]) ? $_COOKIE["date"] : ""?>">
                        </br></br>
                        <div class="row">

                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes('checkboxes1')">
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
                                    <button type="button" id="checkAll1">Отменить всё</button>
                                </div>
                            </div>

                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes('checkboxes2')">
                                    <select>
                                        <option>Категория</option>
                                    </select>
                                    <div class="overSelect"></div>
                                </div>
                                <div id="checkboxes2">
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name FROM Consignors");
                                    $stmt->execute();
                                    $Consignors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($Consignors as $consignor) {
                                        echo '<label for="consignor'.$consignor['id'].'"><input type="checkbox" ';
                                        echo empty($filter_consignor_ids) ? "" : (in_array($consignor['id'], $filter_consignor_ids) ? "checked " : "");
                                        echo 'name="filter_consignor_'.$consignor['id'].'" id="consignor'.$consignor['id'].'">'.$consignor['name'].'</label>';
                                    }
                                    ?>
                                    <button type="button" id="checkAll2">Отменить всё</button>
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
                                <th>Комитент </th>
                                <th>Дата сдачи</th>
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

                    $stmt = $db->prepare("SELECT id, name FROM Consignors");
                    $stmt->execute();
                    $Consignors = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    echo        '<td>'; 
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                                else print(" "); echo 'name="consignor_id'.$value['id'].'">';
                                        foreach ($Consignors as $consignor) {
                                            if ($consignor['id'] == $value['consignor_id']) {
                                                printf('<option selected value="%d">%d. %s</option>', $consignor['id'], $consignor['id'], $consignor['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $consignor['id'], $consignor['id'], $consignor['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';
                    echo        '<td> <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                        else print(" "); echo 'type="date" name="sale_date'.$value['id'].'" value="'.$value['sale_date'].'"> </td>';
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
    document.getElementById('checkAll1').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('#checkboxes1 input[type=checkbox]');
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

    document.getElementById('checkAll2').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('#checkboxes2 input[type=checkbox]');
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