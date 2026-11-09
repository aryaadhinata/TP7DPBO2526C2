<h3>Book List</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>ISBN</th>
        <th>Stock</th>
    </tr>
    <?php foreach ($book->getAllBooks() as $b): ?>
    <tr>
        <td><?= $b['id'] ?></td>
        <td><?= $b['title'] ?></td>
        <td><?= $b['author'] ?></td>
        <td><?= $b['isbn'] ?></td>
        <td><?= $b['stock'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>