<h3>Loan List</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Book</th>
        <th>Member</th>
        <th>Loan Date</th>
        <th>Return Date</th>
        <th>Action</th>
    </tr>
    <?php foreach ($loan->getAllLoans() as $l): ?>
    <tr>
        <td><?= $l['id'] ?></td>
        <td><?= $l['title'] ?></td>
        <td><?= $l['name'] ?></td>
        <td><?= $l['loan_date'] ?></td>
        <td><?= $l['return_date'] ?? 'Not Returned' ?></td>
        <td>
            <?php if (!$l['return_date']): ?>
                <a href="?page=loans&return=<?= $l['id'] ?>">Return</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Borrow a Book</h3>
<form method="POST">
    <label>Book:</label>
    <select name="book_id">
        <?php foreach ($book->getAllBooks() as $b): ?>
            <option value="<?= $b['id'] ?>"><?= $b['title'] ?></option>
        <?php endforeach; ?>
    </select>
    <label>Member:</label>
    <select name="member_id">
        <?php foreach ($member->getAllMembers() as $m): ?>
            <option value="<?= $m['id'] ?>"><?= $m['name'] ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="borrow">Borrow</button>
</form>