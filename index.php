<?php  

require_once __DIR__ . '/vendor/autoload.php';

$authClient = new App\Classes\PlugAndPlayYandexDisk();

$arFiles = $authClient->showAllFiles();
$user = $authClient->getInfoUser();
$page = $authClient->getPage();
$imgType = $authClient->showImgForTypes();
$url = $authClient->showLinkDownloadFile();
$del = $authClient->deleteFile();
$upl = $authClient->uploadFile($_POST);

// Для получения отладочного токена
// https://oauth.yandex.ru/authorize?response_type=token&client_id=
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="assets/css/normalize.css">
	<link rel="stylesheet" href="assets/css/uikit.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<title>Интеграция с Яндекс.Диск</title>
    <!-- Для виджета "Мгновенный вход" <script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js"></script> !-->
</head>
<body>
	<header>
		<div class="uk-container">
			<h1 class="uk-text-large uk-text-center uk-text-primary uk-margin-small-top">Твой mini-Яндекс.Диск <span uk-icon="icon: happy"></span></h1>
			<?php if ($user != ""): ?>
				<p>Чей диск: <span class="uk-text-success uk-text-bold"><?=$user ?></span></p>
			<?php endif ?>
			<?php if ($url) { ?>
				<div class="uk-alert-success uk-width-xlarge" uk-alert>
    				<p class="uk-text-bold">Скопируйте данную ссылку и вставьте в адресную строку браузера для скачивания файла: </p>
    				<textarea class="uk-textarea"><?=$url ?></textarea>
    				<div class="uk-margin-small-top">
    					<a class="uk-button uk-button-primary uk-margin-small-right" href="index.php">ОК</a>
    					<a class="uk-button uk-button-danger" href="index.php">Отмена</a>
    				</div>
				</div>
			<?php } ?>
			<?php if ($del) {
				echo $del;
			} ?>
			<?php if ($upl) {
				echo $upl;
			} ?>
			<div id="modal-upload-file" uk-modal>
    			<div class="uk-modal-dialog uk-modal-body">
        			<button class="uk-modal-close-default" type="button" uk-close></button>
        			<h2 class="uk-modal-title">Загрузка файла</h2>
        			<form action="index.php" enctype="multipart/form-data" method="post">
        				<input type="hidden" name="action" value="upload">
        				<div uk-form-custom="target: true" class="uk-margin-small-bottom">
        					<input type="file" aria-label="Custom controls" name="upload_file">
            				<input class="uk-input uk-form-width-large" type="text" placeholder="Нажмите для выбора" aria-label="Custom controls" disabled>
            			</div>
            			<div class="form-btn">
            				<input type="submit" class="uk-button uk-button-primary" value="Загрузить">
            			</div>
        			</form>
    			</div>
			</div>
			<div class="nav-block">
				<div class="nav-block-btn">
					<button class="uk-button uk-button-default" uk-toggle="target: #modal-upload-file">Загрузить</button>
				</div>
			</div>
		</div>
	</header>
	<main>
		<div class="uk-container">
			<h3 class="uk-h3 uk-margin-medium-top">Файлы</h2>
			<div class="files-block uk-flex uk-flex-wrap uk-padding-small uk-width-xxlarge">
				<?php 
				if (!empty($arFiles)) {
					foreach ($arFiles as $file) {
						?>
						<div class="uk-card uk-card-default uk-flex uk-flex-column uk-text-center uk-margin-small uk-text-center uk-padding-small uk-width-medium uk-card-hover">
							<a class="uk-link-reset" title="Создан: <?=$file["created"] ?>" >
								<img src="<?=$imgType[$file['media_type']] ?>" class="files-block_img uk-margin-small-top">
								<p><?=$file["name"]; ?></p>
							</a>
							<div class="file-block__actions uk-flex uk-flex-center">
								<a class="uk-icon-link uk-icon-button uk-text-secondary uk-margin-small-right" href="index.php?action=download&filePath=<?=$file["path"] ?>" uk-icon="icon: download" uk-toggle="target: #modal-close-default"></a>
        						<a class="uk-icon-link uk-icon-button uk-text-primary uk-margin-small-right" href="<?=$file["docviewer"] ?>" target="_blank" uk-icon="icon: eye"></a>
        						<a class="uk-icon-link uk-icon-button uk-text-danger" href="index.php?action=del&path=<?=$file["path"] ?>" uk-icon="icon: ban"></a>
							</div>
						</div>
						<?php
					}
				} else {
				?>
				<div uk-alert class="uk-alert-danger">
					Файлов нет. Возможно, что-то пошло не так. Или их действительно нет?
				</div>
			<?php 
				} 
			?>
			</div>
			<ul class="uk-flex uk-flex-center uk-pagination">
				<?php if ($page > 1) { ?>
					<li><a href="index.php?page=<?=$page-1 ?>"><span uk-pagination-previous></span>Назад</a></li>
				<?php } ?>
    			<li><a href="index.php?page=<?=$page+1 ?>">Вперёд<span uk-pagination-next></span></a></li>
			</ul>
		</div>
	</main>
	<footer>
		<div class="uk-container">
		</div>
	</footer>
	<!-- Виджет кнопки <script type="text/javascript" src="assets/js/yadi-btn.js"></script> !-->
	<script src="assets/js/uikit.min.js"></script>
    <script src="assets/js/uikit-icons.min.js"></script>
</body>
</html>