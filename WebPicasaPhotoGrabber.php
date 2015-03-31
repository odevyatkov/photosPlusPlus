<?php

require_once('PhotoGrabber.php');

class WebPicasaPhotoGrabber extends PhotoGrabber
{
    public function __construct()
    {
    }

    public function saveAllByUserId($userId, $offset = 1, $limit = 5)
    {
        $data = $this->getJsonFromUrl($this->getAllAlbumsUrl($userId, $offset, $limit));

        $tValue = '$t';
        $openSearchTotalResults = 'openSearch$totalResults';
        $openSearchStartIndex = 'openSearch$startIndex';
        $openSearchItemsPerPage = 'openSearch$itemsPerPage';
        $gphotoId= 'gphoto$id';

        $user = [
            'updateUrl' => '',
        ];
        $albums = [];
        foreach ($data->feed->link as $curLink) {
            if ($curLink->rel == 'self' && $curLink->type == 'application/atom+xml') {
                $user['updateUrl'] = $curLink->href;
            }
        }

        foreach ($data->feed->entry as $curAlbum) {
            $album = [
                'id' => $curAlbum->$gphotoId->$tValue,
                'published' => $curAlbum->published->$tValue,
                'updated' => $curAlbum->updated->$tValue,
                'title' => $curAlbum->title->$tValue,
                'subTitle' => isset($curAlbum->subtitle->$tValue) ? $curAlbum->subtitle->$tValue : '',
                'summary' => $curAlbum->summary->$tValue,
                'rights' => $curAlbum->rights->$tValue,
                'updateUrl' => '',
                'authors' => [],
            ];
            foreach ($curAlbum->link as $curLink) {
                if ($curLink->rel == 'self' && $curLink->type == 'application/atom+xml') {
                    $album['updateUrl'] = $curLink->href;
                }
            }
            foreach ($curAlbum->author as $curAuthor) {
                $album['authors'][] = [
                    'name' => $curAuthor->name->$tValue,
                    'uri' => $curAuthor->uri->$tValue,
                ];
            }
            $this->saveAllFromAlbum($userId, $album['id']);
            $albums[] = $album;
        }

        $totalCont = $data->feed->$openSearchTotalResults->$tValue;
        $lastOnCurrentPage = $data->feed->$openSearchStartIndex->$tValue + $data->feed->$openSearchItemsPerPage->$tValue + 1;
        if ($totalCont > $lastOnCurrentPage) {
            $this->saveAllByUserId($userId, $offset+$limit, $limit);
        }
        print_r($albums);
    }

    public function saveAllFromAlbum($userId, $albumId, $offset = 1, $limit = 1000)
    {
        $data = $this->getJsonFromUrl($this->getAlbumUrl($userId, $albumId, $offset, $limit));

        $tValue = '$t';
        $openSearchTotalResults = 'openSearch$totalResults';
        $openSearchStartIndex = 'openSearch$startIndex';
        $openSearchItemsPerPage = 'openSearch$itemsPerPage';
        $gphotoAccess = 'gphoto$access';
        $gphotoId = 'gphoto$id';
        $gphotoAlbumId = 'gphoto$albumid';
        $gphotoWidth = 'gphoto$width';
        $gphotoHeight = 'gphoto$height';
        $gphotoTimestamp = 'gphoto$timestamp';
        $gphotoLicense = 'gphoto$license';
        $exifTags = 'exif$tags';
        $mediaGroup = 'media$group';
        $mediaContent = 'media$content';
        $mediaThumbnail = 'media$thumbnail';
        $gphotoCommentCount = 'gphoto$commentCount';
        $exifFstop = 'exif$fstop';
        $exifMake = 'exif$make';
        $exifModel = 'exif$model';
        $exifExposure = 'exif$exposure';
        $exifFlash = 'exif$flash';
        $exifFocallength = 'exif$focallength';
        $exifIso = 'exif$iso';
        $georssWhere = 'georss$where';
        $gmlPoint = 'gml$Point';
        $gmlPos = 'gml$pos';

        $photos = [];
        foreach ($data->feed->entry as $curPhoto) {
            if ($curPhoto->content->type === 'image/jpeg') {
                $photo = [
                    'id' => $curPhoto->$gphotoId->$tValue,
                    'published' => $curPhoto->published->$tValue,
                    'updated' => $curPhoto->updated->$tValue,
                    'access' => $curPhoto->$gphotoAccess->$tValue,//public or private
                    'summary' => $curPhoto->summary->$tValue,
                    'updateUrl' => '',
                    'albumId' => $curPhoto->$gphotoAlbumId->$tValue,
                    'width' => $curPhoto->$gphotoWidth->$tValue,
                    'height' => $curPhoto->$gphotoHeight->$tValue,
                    'timestamp' => $curPhoto->$gphotoTimestamp->$tValue,
                    'license' => $curPhoto->$gphotoLicense->$tValue,
                    'exifTags' => [],
                    'originUrl' => $curPhoto->content->src,
                    'thumbnails' => [],
                    'commentsCount' => $curPhoto->$gphotoCommentCount->$tValue,
                    'geo' => [],
                ];
                foreach ($curPhoto->link as $curLink) {
                    if ($curLink->rel == 'self' && $curLink->type == 'application/atom+xml') {
                        $photo['updateUrl'] = $curLink->href;
                    }
                }

                if (isset($curPhoto->$exifTags->$exifFstop)) {
                    $photo['exifTags']['fstop'] = $curPhoto->$exifTags->$exifFstop->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifMake)) {
                    $photo['exifTags']['make'] = $curPhoto->$exifTags->$exifMake->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifModel)) {
                    $photo['exifTags']['model'] = $curPhoto->$exifTags->$exifModel->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifExposure)) {
                    $photo['exifTags']['exposure'] = $curPhoto->$exifTags->$exifExposure->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifFlash)) {
                    $photo['exifTags']['flash'] = $curPhoto->$exifTags->$exifFlash->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifFocallength)) {
                    $photo['exifTags']['focallength'] = $curPhoto->$exifTags->$exifFocallength->$tValue;
                }
                if (isset($curPhoto->$exifTags->$exifIso)) {
                    $photo['exifTags']['iso'] = $curPhoto->$exifTags->$exifIso->$tValue;
                }

                foreach ($curPhoto->$mediaGroup->$mediaThumbnail as $curMediaThumbnail) {
                    $curIndex = ($curMediaThumbnail->height > $curMediaThumbnail->width)
                        ? $curMediaThumbnail->height
                        : $curMediaThumbnail->width;
                    $photo['thumbnails'][$curIndex] = $curMediaThumbnail->url;
                }
                if (isset($curPhoto->$georssWhere)) {
                    $coordiname = explode(" ", $curPhoto->$georssWhere->$gmlPoint->$gmlPos->$tValue);
                    $photo['geo'] = [
                        'latitude' => $coordiname[0],
                        'longitude' => $coordiname[1],
                    ];
                }
                $photos[] = $photo;
            }
        }
        print_r($photos);

        $totalCont = $data->feed->$openSearchTotalResults->$tValue;
        $lastOnCurrentPage = $data->feed->$openSearchStartIndex->$tValue + $data->feed->$openSearchItemsPerPage->$tValue + 1;
        if ($totalCont > $lastOnCurrentPage) {
            $this->saveAllFromAlbum($userId, $albumId, $offset + $limit, $limit);
        }
    }

    protected function getJsonFromUrl($url)
    {
        try {
            $content = file_get_contents($url);
        } catch (Exception $e) {
            //@todo: use other Exception type
            throw new Exception(
                'Can`t get data from url'
            );
        }

        try {
            return json_decode($content);
        } catch (Exception $e) {
            //@todo: use other Exception type
            throw new Exception(
                'Can`t decode json'
            );
        }
    }

    protected function getAllAlbumsUrl($userId, $offset = 1, $limit = 1000)
    {
        return "https://picasaweb.google.com/data/feed/api/user/" . $userId . "/"
            . "?alt=json&start-index=" . $offset . "&max-results=" . $limit;
    }

    protected function getAlbumUrl($userId, $albumId, $offset = 1, $limit = 1000)
    {
        return "https://picasaweb.google.com/data/feed/api/user/" . $userId . "/albumid/" . $albumId
            . "?alt=json&start-index=" . $offset . "&max-results=" . $limit;
    }
}