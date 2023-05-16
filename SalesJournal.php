<?php

include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, product_id, consignor_id, sale_date FROM SalesJournal");
        $params = [];

        if (!empty($_COOKIE['products'])) {
            $filter_product_ids = unserialize($_COOKIE['products']);
            $in_values1 = implode(',', array_fill(0, count($filter_product_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND product_id IN ($in_values1)" : "product_id IN ($in_values1)";
            $params = array_merge($params, $filter_product_ids);
        }

        if (!empty($_COOKIE['consignors'])) {
            $filter_consignor_ids = unserialize($_COOKIE['consignors']);
            $in_values2 = implode(',', array_fill(0, count($filter_consignor_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND consignor_id IN ($in_values2)" : "consignor_id IN ($in_values2)";
            $params = array_merge($params, $filter_consignor_ids);
        }

        if (!empty($_COOKIE['date'])) {
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND sale_date = ?" : "sale_date = ?";
            $params[] = $_COOKIE['date'];
        }

        if (isset($stmt_sql)) {
            $stmt_sql = "SELECT id, product_id, consignor_id, sale_date FROM SalesJournal WHERE ".$stmt_sql;
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

            $stmt = $db->prepare("SELECT id FROM Consignors");
            $stmt->execute();
            $c_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filter_consignor_ids = [];
            foreach ($c_ids as $c_id) {
                $filter_consignor_ids[] = $c_id['id'];
            }
        }
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    $new = array();
    $new['product_id'] = empty($_COOKIE['product_id']) ? '' : $_COOKIE['product_id'];
    $new['consignor_id'] = empty($_COOKIE['consignor_id']) ? '' : $_COOKIE['consignor_id'];
    $new['sale_date'] = empty($_COOKIE['sale_date']) ? '' : $_COOKIE['sale_date'];
    include('SalesJournal/main.php');
} else {
    $errors = array();
    $messages = array();
    if (!empty($_POST['addnewdate'])) {
        if (empty($_POST['product_id'])) {
            $errors['product_id'] = "Не выбран товар";
        } else {
            setcookie('product_id', $_POST['product_id'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['consignor_id'])) {
            $errors['consignor_id'] = "Не выбран комитент";
        } else {
            setcookie('consignor_id', $_POST['consignor_id'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['sale_date'])) {
            $errors['sale_date'] = "Не выбрана дата сдачи";
        } else {
            setcookie('sale_date', $_POST['sale_date'], time() + 24 * 60 * 60);
        }
        if (empty($errors)) {
            $product_id = $_POST['product_id'];
            $consignor_id = $_POST['consignor_id'];
            $sale_date = $_POST['sale_date'];
            $stmt = $db->prepare("INSERT INTO SalesJournal (product_id, consignor_id, sale_date) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $consignor_id, $sale_date]);
            $messages['added'] = 'Данные успешно добавлены';
            setcookie('product_id', '', time() + 24 * 60 * 60);
            setcookie('consignor_id', '', time() + 24 * 60 * 60);
            setcookie('sale_date', '', time() + 24 * 60 * 60);
        }
    }
    foreach ($_POST as $key => $value) {
        if (preg_match('/^clear(\d+)_x$/', $key, $matches)) {
            $id = $matches[1];
            $stmt = $db->prepare("DELETE FROM SalesJournal WHERE id = ?");
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
            $stmt = $db->prepare("SELECT product_id, consignor_id, sale_date FROM SalesJournal WHERE id = ?");
            $stmt->execute([$id]);
            $old_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dates['product_id'] = $_POST['product_id' . $id];
            $dates['consignor_id'] = $_POST['consignor_id' . $id];
            $dates['sale_date'] = $_POST['sale_date' . $id];

            if (array_diff_assoc($dates, $old_dates[0])) {
                setcookie('idit', '1', time() + 24 * 60 * 60);
                $stmt = $db->prepare("UPDATE SalesJournal SET product_id = ?, consignor_id = ?, sale_date = ? WHERE id = ?");
                $stmt->execute([$dates['product_id'], $dates['consignor_id'], $dates['sale_date'], $id]);
                $messages['edited'] = 'Данные успешно обновлёны';
            }
        }
    }

    if (!empty($_POST['resetall'])) {
        setcookie('date', '');
        setcookie('products', '');
        setcookie('consignors', '');
    }

    if (!empty($_POST['filter'])) {

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

        $filter_consignor_ids = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'filter_consignor_') !== false) {
                $id = substr($key, 17);
                $filter_consignor_ids[] = $id;
            }
        }
        setcookie('consignors', serialize($filter_consignor_ids));
        
    }
    if (!empty($messages)) {
        setcookie('messages', serialize($messages), time() + 24 * 60 * 60);
    }
    if (!empty($errors)) {
        setcookie('errors', serialize($errors), time() + 24 * 60 * 60);
    }
    header('Location: SalesJournal.php');
}