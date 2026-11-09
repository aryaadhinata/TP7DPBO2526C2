<?php
require_once 'class/Book.php';
require_once 'class/Member.php';
require_once 'class/Loan.php';

$book = new Book();
$member = new Member();
$loan = new Loan();

if (isset($_POST['borrow'])) {
    $loan->borrowBook($_POST['book_id'], $_POST['member_id']);
}
if (isset($_GET['return'])) {
    $loan->returnBook($_GET['return']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'view/header.php'; ?>
    <main>
        <h2>Welcome to Library System</h2>
        <nav>
            <a href="?page=books">Books</a> |
            <a href="?page=members">Members</a> |
            <a href="?page=loans">Loans</a>
        </nav>

        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            if ($page == 'books') include 'view/books.php';
            elseif ($page == 'members') include 'view/members.php';
            elseif ($page == 'loans') include 'view/loans.php';
        }
        ?>
    </main>
    <?php include 'view/footer.php'; ?>
</body>
</html>