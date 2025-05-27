<style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        padding: 30px;
    }
    form {
        background: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px #ccc;
    }
    input, textarea, button {
        width: 100%;
        margin: 10px 0;
        padding: 10px;
    }
    button {
        background: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>



<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "blog_app");
$user_id = $_SESSION["user_id"];

// CREATE NEW POST
if (isset($_POST["new_post"])) {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $stmt = $conn->prepare("INSERT INTO posts (title, content, created_by, updated_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $content, $user_id, $user_id);
    $stmt->execute();
}

// UPDATE POST
if (isset($_POST["update_post"])) {
    $post_id = $_POST["post_id"];
    $title = $_POST["title"];
    $content = $_POST["content"];
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssii", $title, $content, $user_id, $post_id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

// DELETE POST
if (isset($_GET["delete_post"])) {
    $id = $_GET["delete_post"];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

// ADD COMMENT
if (isset($_POST["new_comment"])) {
    $post_id = $_POST["post_id"];
    $comment = $_POST["comment"];
    $stmt = $conn->prepare("INSERT INTO comments (post_id, comment, created_by, updated_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $post_id, $comment, $user_id, $user_id);
    $stmt->execute();
}

// FETCH POSTS
$posts = $conn->query("SELECT posts.*, u.username AS author FROM posts LEFT JOIN users u ON posts.created_by = u.id ORDER BY posts.id DESC");
?>

<h2>Welcome, <?= $_SESSION["username"] ?> | <a href="logout.php">Logout</a></h2>

<h3>Create a Post</h3>
<form method="post">
    Title: <input name="title" required><br>
    Content: <textarea name="content" required></textarea><br>
    <button name="new_post">Add Post</button>
</form>

<hr>
<h3>All Posts</h3>
<?php while ($row = $posts->fetch_assoc()): ?>
    <div style="border:1px solid gray; padding:10px; margin-bottom:10px;">
        <strong><?= htmlspecialchars($row["title"]) ?></strong>
        by <?= $row["author"] ?> <br>
        <small>
            <?= date("M d, Y h:i A", strtotime($row["created_at"])) ?>
            <?php if ($row["created_at"] != $row["updated_at"]) echo "(EDITED)"; ?>
        </small>
        <p><?= nl2br(htmlspecialchars($row["content"])) ?></p>

        <?php if ($row["created_by"] == $user_id): ?>
            <?php if (isset($_GET["edit_post"]) && $_GET["edit_post"] == $row["id"]): ?>
                <form method="post">
                    <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                    Title: <input name="title" value="<?= htmlspecialchars($row['title']) ?>" required><br>
                    Content: <textarea name="content" required><?= htmlspecialchars($row['content']) ?></textarea><br>
                    <button name="update_post">Update Post</button>
                    <a href="index.php" style="margin-left:10px;">Cancel</a>
                </form>
            <?php else: ?>
                <a href="?edit_post=<?= $row['id'] ?>">Edit Post</a> |
                <a href="?delete_post=<?= $row['id'] ?>" onclick="return confirm('Delete post?')">Delete Post</a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Add Comment -->
        <form method="post" style="margin-top:10px;">
            <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
            <textarea name="comment" placeholder="Write a comment..." required></textarea><br>
            <button name="new_comment">Comment</button>
        </form>

        <!-- Show Comments -->
        <div style="margin-left:20px; margin-top:10px;">
            <?php
            $pid = $row["id"];
            $comments = $conn->query("SELECT comments.*, u.username FROM comments LEFT JOIN users u ON comments.created_by = u.id WHERE post_id = $pid ORDER BY comments.id ASC");
            while ($com = $comments->fetch_assoc()):
                $parsed_comment = htmlspecialchars($com["comment"]);
                $parsed_comment = preg_replace_callback('/@(\w+)/', function($matches) use ($conn) {
                    $mention = $matches[1];
                    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
                    $stmt->bind_param("s", $mention);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        return "<strong>@{$mention}</strong>";
                    } else {
                        return "@{$mention}";
                    }
                }, $parsed_comment);
            ?>
                <div style="border-top:1px solid #ccc; margin-top:5px; padding-top:5px;">
                    <strong><?= $com["username"] ?>:</strong>
                    <?= nl2br($parsed_comment) ?>
                    <br><small><?= date("M d, Y h:i A", strtotime($com["created_at"])) ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endwhile; ?>
