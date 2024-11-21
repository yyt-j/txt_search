<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>搜索TXT文件内容 - 便捷搜索工具</title>
    <!-- 可根据需求修改具体标题内容 -->
<link rel="stylesheet" type="text/css" href="/css/global.css">
</head>

<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SESELF"]);?>" method="post">
        <input type="text" name="search" placeholder="输入搜索内容">
        <input type="submit" value="搜索">
    </form>
    <?php
    // 用于缓存文件内容的数组
    $fileContentsCache = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $search_term = $_POST["search"];
        if (empty($search_term)) {
            echo '<div class="empty-tip">不能输入空</div>';
            return;
        }
        $directory = "test/";
        $files = glob($directory. "*.txt");

        // 用于存储按文件名归类后的结果
        $groupedResults = [];

        foreach ($files as $file) {
            // 检查文件内容是否已在缓存中
            if (!isset($fileContentsCache[$file]) && file_exists($file)) {
                // 如果不在缓存，读取文件内容并 cache 起来
                $fileContentsCache[$file] = file($file);
            }

            $lines = $fileContentsCache[$file];

            foreach ($lines as $line_number => $line) {
                if (mb_strpos(mb_strtolower($line), mb_strtolower($search_term))!== false) {
                    $fileName = basename($file);
                    if (!isset($groupedResults[$fileName])) {
                        $groupedResults[$fileName] = [];
                    }
                    $groupedResults[$fileName][] = [
                        'line_number' => $line_number + 1,
                        'line_content' => $line
                    ];
                }
            }
        }

        $result_output = "";
        foreach ($groupedResults as $fileName => $lines) {
            $result_output.= '<div class="result-file-item">';
            $result_output.= '<span class="result-file-name">文件名: '. $fileName. '</span>';
            foreach ($lines as $lineInfo) {
                $line_content = $lineInfo['line_content'];
                // 对搜索词进行转义处理，以便在正则表达式中正确使用
                $escaped_search_term = preg_quote(mb_strtolower($search_term), '/');
                // 使用preg_replace进行更精准的匹配和替换，突出显示匹配关键词
                $highlighted_content = preg_replace('/'. $escaped_search_term. '/i', '<span class="highlight">'. mb_strtolower($search_term). '</span>', mb_strtolower($line_content));
                $result_output.= '<div class="result-line">行号 '. $lineInfo['line_number']. ': &nbsp&nbsp&nbsp'. $highlighted_content. '</div>';
            }
            $result_output.= '</div>';
        }

        if (empty($result_output)) {
            echo '<div class="no-match-tip">未找到匹配内容。</div>';
        }

        echo '<div class="result-area">'. $result_output. '</div>';
    }
?>
</body>

</html>
