<h3>Member List</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
    </tr>
    <?php foreach ($member->getAllMembers() as $m): ?>
    <tr>
        <td><?= $m['id'] ?></td>
        <td><?= $m['name'] ?></td>
        <td><?= $m['email'] ?></td>
        <td><?= $m['phone'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>