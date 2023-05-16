<?php

include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, product_id, purchase_date, price FROM PurchaseJournal");
        $params = [];
        
        if (!empty($_COOKIE['range'])) {
            $range = unserialize($_COOKIE['range']);
            list($range1, $range2) = explode(' - ', $range);
            $range1 = str_replace(' ₽', '', $range1);
            $range2 = str_replace(' ₽', '', $range2);
            $stmt_sql = "price > ? AND price < ?";
            $params = array_merge($params, [$range1, $range2]);
        }
        
        if (!empty($_COOKIE['date'])) {
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND purchase_date = ?" : "purchase_date = ?";
            $params[] = $_COOKIE['date'];
        }
        
        if (!empty($_COOKIE['products'])) {
            $filter_product_ids = unserialize($_COOKIE['products']);
            $in_values = implode(',', array_fill(0, count($filter_product_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND product_id IN ($in_values)" : "product_id IN ($in_values)";
            $params = array_merge($params, $filter_product_ids);
        }
        
        if (isset($stmt_sql)) {
            $stmt_sql = "SELECT id, product_id, purchase_date, price FROM PurchaseJournal WHERE ".$stmt_sql;
            $stmt = $db->prepare($stmt_sql);
            $stmt->execute($params);
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt->execute();
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $db->prepare("SELECT id FROM Products");
            $stmt->execute();
            $p_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filter_product_ids = [];
            foreach ($p_ids as $p_id) {
                $filter_product_ids[] = $p_id['id'];
            }
        }
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    $new = array();
    $new['product_id'] = empty($_COOKIE['product_id']) ? '' : $_COOKIE['product_id'];
    $new['price'] = empty($_COOKIE['price']) ? '' : $_COOKIE['price'];
    $new['purchase_date'] = empty($_COOKIE['purchase_date']) ? '' : $_COOKIE['purchase_date'];
    include('PurchaseJournal/main.php');
} else {
    $errors = array();
    $messages = array();
    if (!empty($_POST['addnewdate'])) {
        if (empty($_POST['product_id'])) {
            $errors['product_id'] = "Не выбран товар";
        } else {
            setcookie('product_id', $_POST['product_id'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['price'])) {
            $errors['price'] = "Не выбрана цена товара";
        } else if (!is_numeric($_POST['price'])) {
            $errors['price2'] = "Неизвестный формат цены товара";
        } else {
            setcookie('price', $_POST['price'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['purchase_date'])) {
            $errors['purchase_date'] = "Не выбрана дата продажи";
        } else {
            setcookie('purchase_date', $_POST['purchase_date'], time() + 24 * 60 * 60);
        }

        if (empty($errors)) {
            $stmt = $db->prepare("SELECT MIN(sale_date) FROM SalesJournal WHERE product_id = ?");
            $stmt->execute([$_POST['product_id']]);
            $sale_date = $stmt->fetchColumn();
            if (empty($sale_date)) {
                $errors['purchase_date2'] = "Вы не можете продать этот товар т.к. его ещё не сдали";
            }
            if (strtotime($sale_date) > strtotime($_POST['purchase_date'])) {
                $errors['purchase_date3'] = "Вы не можете продать этот товар раньше чем ". date("d.m.Y", strtotime($sale_date));
            }
        }
        
        if (empty($errors)) {
            $product_id = $_POST['product_id'];
            $purchase_date = $_POST['purchase_date'];
            $price = $_POST['price'];

            $stmt = $db->prepare("INSERT INTO PurchaseJournal (product_id, purchase_date, price) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $purchase_date, $price]);
            $messages['added'] = 'Данные успешно добавлены';

            $stmt = $db->prepare("SELECT MIN(sale_date) FROM SalesJournal WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $sale_date = $stmt->fetchColumn();

            $stmt = $db->prepare("SELECT MIN(id) FROM SalesJournal WHERE product_id = ? and sale_date = ?");
            $stmt->execute([$product_id, $sale_date]);
            $min_id = $stmt->fetchColumn();

            $stmt = $db->prepare("DELETE FROM SalesJournal WHERE id = ?");
            $stmt->execute([$min_id]);

            setcookie('product_id', '', time() + 24 * 60 * 60);
            setcookie('price', '', time() + 24 * 60 * 60);
            setcookie('purchase_date', '', time() + 24 * 60 * 60);
        }
    }
    
    foreach ($_POST as $key => $value) {
        if (preg_match('/^clear(\d+)_x$/', $key, $matches)) {
            $id = $matches[1];
            $stmt = $db->prepare("DELETE FROM PurchaseJournal WHERE id = ?");
            $stmt->execute([$id]);
            $messages['deleted'] = 'Данные успешно удалены';
        }
        if (preg_match('/^edit(\d+)_x$/', $key, $matches)) {
            $id = $matches[1];
            setcookie('edit', $id, time() + 24 * 60 * 60);
        }
        if (preg_match('/^save(\d+)_x$/', $key, $matches)) {
            setcookie('edit', '', time() + 24 * 60 * 60);
            $id = $matches[1];
            $stmt = $db->prepare("SELECT product_id, purchase_date, price FROM PurchaseJournal WHERE id = ?");
            $stmt->execute([$id]);
            $old_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dates['product_id'] = $_POST['product_id' . $id];
            $dates['purchase_date'] = $_POST['purchase_date' . $id];
            $dates['price'] = $_POST['price' . $id];

            if (array_diff($dates, $old_dates[0])) {
                $stmt = $db->prepare("UPDATE PurchaseJournal SET product_id = ?, purchase_date = ?, price = ? WHERE id = ?");
                $stmt->execute([$dates['product_id'], $dates['purchase_date'], $dates['price'], $id]);
                $messages['edited'] = 'Данные успешно обновлёны';
            }
        }
    }
    if (!empty($_POST['resetall'])) {
        setcookie('date', '');
        setcookie('range', '');
        setcookie('products', '');
    }

    if (!empty($_POST['filter'])) {
        if (!empty($_POST['range']))
            setcookie('range', serialize($_POST['range']));

        if (!empty($_POST['date']))
            setcookie('date', $_POST['date']);

        $filter_product_ids = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'filter_product_') !== false) {
                $id = substr($key, 15);
                $filter_product_ids[] = $id;
            }
        }
        setcookie('products', serialize($filter_product_ids));
    }
    if (!empty($messages)) {
        setcookie('messages', serialize($messages), time() + 24 * 60 * 60);
    }
    if (!empty($errors)) {
        setcookie('errors', serialize($errors), time() + 24 * 60 * 60);
    }
    header('Location: PurchaseJournal.php');
}