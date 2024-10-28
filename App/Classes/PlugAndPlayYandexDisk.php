<?php

namespace App\Classes;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use Arhitector\Yandex\Disk\Resource\Opened;
use Arhitector\Yandex\Disk\Resource\Removed;
use Arhitector\Yandex\Disk\Resource\Collection;
use Laminas\Diactoros\Stream;

class PlugAndPlayYandexDisk
{
	private $token = null;

	function __construct($token = null)
	{
		if ($this->token === null) {
			$this->token = self::getTimeToken($token);
		}
		
		return $token;
	}

	public function initDisk()
	{
		$disk = '';

		if ($this->token != null) {
			$disk = new \Arhitector\Yandex\Disk($this->token);
		}
		
		return $disk;
	}
	/*
	 * Возвращает информацию о пользователе
	 */
	public function getInfoUser()
	{
		$res = "";
		$disk = self::initDisk();
		$user = $disk->get('user');

		if ($user["display_name"]) {
			$res = $user["display_name"];
		} else {
			$res = $user["login"];
		}

		return $res;
	}

	public function showAllFiles(): array
	{
		$page = self::getPage();
		
		if ($page) {
			$resources = self::getAllFiles($page);
		}

		return $resources;
	}

	/*
	 * Возвращает массив файлов 
	 *	limit - количество записей на стр.
	 * 	m - множитель (увеличивает пропуск(offset) записей для каждой страницы)	
	 */
	public function getAllFiles($page): array
	{
		$disk = self::initDisk($this->token);
		$resources = [];
		$offset = 0;
		$limit = 10;
		$m = 10;

		$offset = $page * $m;
		$res = $disk->getResources()
			->setLimit($limit)
			->setOffset($offset)
			->setSort('name', true)->toArray();
		if ($res) {
			foreach ($res as $object) {
				$resources[] = $object->toArray();
			}
		} else {
			$resources = [];
		}
		return $resources;
	}

	public function getPage(): int
	{
		$page = 1;

		if (!empty($_GET['page'])) {
			$page = $_GET['page'];
		}

		return $page;
	}

	public function getFileTypes(): array
	{
		$types = [];
		$disk = self::initDisk($this->token);
		$types = $disk->getResources()->getMediaTypes();

		return $types;
	}

	public function showImgForTypes(): array
	{
		$arSrc = [];
		$page = self::getPage();
		$res = self::getFileTypes();

		foreach ($res as $key => $value) {
			$arSrc[$value] = '/assets/img/' . $value . '.png';
		}

		return $arSrc;
	}

	public function showLinkDownloadFile()
	{
		$link = "";
		$page = self::getPage();
		$disk = self::initDisk($this->token);
		$files = self::getAllFiles($page);

		if (array_key_exists("filePath", $_GET) == true) {
			if ($_GET["action"] == "download") {
				foreach ($files as $file) {
					if ($file["path"] == $_GET["filePath"]) {
						$link = self::getLinkDownloadFile();
					}
				}
			}
		}

		return $link;
	}

	public function uploadFile($data)
	{
		$result = "";
		$disk = self::initDisk($this->token);
		$nameFile = "";

		if (array_key_exists("action", $data)) {
			if ($data["action"] == "upload") {
				if ($_FILES["upload_file"]) {
					$nameFile = $_FILES["upload_file"]["name"];
					$resource = $disk->getResource($nameFile);
					$resource->has();

					if ($resource->has() === false) {
						$resource->upload($_FILES["upload_file"]["tmp_name"], true);
						$status = true; // Файл создан (зелёный свет)
						$result = self::showStatusUploadFile($status, $nameFile);
					} else {
						$status = false; // Упс.. (красный свет)
						$result = self::showStatusUploadFile($status, $nameFile);
					}
				}
			}
		}

		return $result;
	}

	public function deleteFile()
	{
		$result = '';
		$disk = self::initDisk($this->token);
		$page = self::getPage();
		$files = self::getAllFiles($page);

		if (array_key_exists("action", $_GET) == true && array_key_exists("path", $_GET) == true) {
			if ($_GET["action"] == "del") {
				$resource = $disk->getResource($_GET["path"]);
				$resource->has();

				if ($resource->has() === true) {
					$resource->delete();
					$status = true; // Файл найден (зелёный свет)
					$result = self::showStatusDelFile($status);
				} else {
					$status = false; // Упс.. (красный свет)
					$result = self::showStatusDelFile($status);
				}
			}
		}

		return $result;
	}

	private function setCurlOptions($url, $arParams)
	{
		$curlOptions = [];
		$headers = [
			"Authorization: OAuth {$this->token}",
			'Content-Type: application/json',
		];

		$curlOptions = [
			CURLOPT_URL => $url . http_build_query($arParams),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => $headers,
		];
		
		return $curlOptions;
	}

	private function getLinkDownloadFile(): string
	{
		$link = "";
		$url = 'https://cloud-api.yandex.net/v1/disk/resources/download?';
		$arParams = [
			'path' => $_GET["filePath"],
			'fields' => ''
		];
		$curlOptions = self::setCurlOptions($url, $arParams);
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$path = curl_exec($ch);
		$path = get_object_vars(json_decode($path));
		if ($path['href']) {
			$link = $path['href'];
		}
		curl_close($ch);

		return $link;
	}

	private function showStatusDelFile($status): string
	{
		$result = "";

		if ($status === true) {
			$result = '<div class="uk-alert-success uk-width-xlarge" uk-alert>
    						<p>Файл успешно помещён в корзину!</p>
    						<a class="uk-button uk-button-default" href="index.php">ОК</a>
						</div>';
		} else {
			$result = '<div class="uk-alert-danger uk-width-xlarge" uk-alert>
    						<p>Упс! Файл удалить не получилось</p>
    						<a class="uk-button uk-button-default" href="index.php">ОК</a>
						</div>';
		}

		return $result;
	}

	private function showStatusUploadFile($status, $nameFile): string
	{
		$result = "";

		if ($status == true) {
			$result = '<div class="uk-alert-success uk-width-xlarge" uk-alert>
    						<p>Файл с именем ' . $nameFile . ' успешно добавлен!</p>
    						<a class="uk-button uk-button-default" href="index.php">ОК</a>
						</div>';
		} else {
			$result = '<div class="uk-alert-danger uk-width-xlarge" uk-alert>
    						<p>Файл с именем ' . $nameFile . ' не удалось добавить!</p>
    						<p>Возможные причины:</p>
    						<ul>
    							<li>файл с таким именем уже существует;</li>
    							<li>размер файла больше допустимого (зависит от наличия подписки);</li>
    							<li>не хватает места.</li>
    						</ul>
    						<a class="uk-button uk-button-danger" href="index.php">ОК</a>
						</div>';
		}

		return $result;
	}

	private function getTimeToken($token)
	{
		// Отладочный токен
		$token = '';

		return $token;
	}
}