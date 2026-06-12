<?php
namespace App\Http\Traits;

use App\Models\Notifications;
use App\Models\Orders;
use App\Models\TriniteqOrders;
use App\Notifications\DeviceIdsFCM;
use App\Notifications\TopicFCM;
use App\Models\User;
use App\Models\NotificationMessage;
use App\Models\Round;
use App\Models\MatchRound;
use App\Models\Prediction;
use App\Models\RoomRuleSet;
use App\Models\BettingRoom;
use App\Models\Rooms;
use App\Models\LoginTrack;
use App\Models\RoomArchived;
use App\Models\UsersVerify;
use Illuminate\Support\Facades\Http;
use DB;
use Image;

trait UtilityFunction
{

    protected function sendSMS($mobile, $message, $dlttempid){
        $msg = strip_tags($message);
        $user = "afd322-950e7e-aa8b68-834196-8162a5";
        $receipientno = str_replace('+', '', $mobile);
        $senderID = "PPNSPL";
        $msgtxt = $msg;
        return $response = Http::get('https://api.pinnacle.in/index.php/sms/urlsms', [
            "sender" => $senderID,
            "numbers" => $receipientno,
            "message" => $msgtxt,
            "messagetype" => 'TXT',
            "response" => 'Y',
            "apikey" => $user,
            "dlttempid" => $dlttempid
        ]);
    }
    
    /**
     * For sending fcm notification and sms to specific users
     *
     * @param String $title title of notification
     * @param String $body body message of notification
     * @param Integer $user_id user id to whom notification will be sent
     * @param Integer $task_id task id (group_invitation, ride_invitation_id)
     * @param String $task_type type of task need to perform (group_invitaion, ride_invitation)
     * @return Boolean false when notifications sent successfully
     */
    protected function sendDeviceNotification($title, $body, $user_id, $service_id, $service_type, $sender_id = 0)
    {
        $user = User::where('id', $user_id)->first();
        if ($user) {
            $notification = NotificationMessage::where('sender_id', $sender_id)
                                                ->where('receiver_id', $user->id)
                                                ->where('service_id', $service_id)
                                                ->where('service_type', $service_type)
                                                ->where('title', $title)
                                                ->where('message', $body)
                                                ->whereDate('created_at', date('Y-m-d'))
                                                ->first();
            if(!$notification){
                $notification = new NotificationMessage();
                $notification->sender_id = $sender_id;
                $notification->receiver_id = $user->id;
                $notification->service_id = $service_id;
                $notification->service_type = $service_type;
                $notification->title = $title;
                $notification->message = $body;
                $notification->save();
                
                if($user->devices->first()){
                    if($user->devices->where('device_type', 'Android')->first())
                    \Notification::send($user, new DeviceIdsFCM($user->devices->where('device_type', 'Android')->where('device_token', '!=', NULL)->pluck('device_token')->toArray(),$title,strip_tags($body),$service_id,$service_type, 'Android'));
                    if($user->devices->where('device_type', 'IOS')->first())
                    \Notification::send($user, new DeviceIdsFCM($user->devices->where('device_type', 'IOS')->where('device_token', '!=', NULL)->pluck('device_token')->toArray(),$title,strip_tags($body),$service_id,$service_type, 'IOS'));
                    if($user->devices->where('device_type', 'Web')->first())
                    \Notification::send($user, new DeviceIdsFCM($user->devices->where('device_type', 'Web')->where('device_token', '!=', NULL)->pluck('device_token')->toArray(),$title,strip_tags($body),$service_id,$service_type, 'Web'));
                }
            }
        }
        return true;
    }

    /**
     * For deleting images from directory
     *
     * @param String $image image name
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function deleteImage($image, $imagePath, $resizeArr)
    {
        \Storage::delete($imagePath . '/' . $image);
        foreach ($resizeArr as $imagePrefix => $sizes) {
            \Storage::disk('public')->delete($imagePath . '/' . $imagePrefix . '-' . $image);
        }
    }

    /**
     * For deleting video from directory
     *
     * @param String $video video name
     * @param String $videoPath video path
     */
    public function deleteVideo($video, $videoPath)
    {
        $videoImage = $this->getVideoImage($video);
        \Storage::disk('public')->delete($videoPath . '/' . $video);
        \Storage::disk('public')->delete($videoPath . '/' . $videoImage);
    }

    /**
     * For resizing images
     *
     * @param String $image image object
     * @param String $imageName image name
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function ImageResize($image, $imageName, $resizeArr, $imagePath)
    {

        foreach ($resizeArr as $imagePrefix => $sizes) {

            $resizeImgName = $imagePrefix . '-' . $imageName;

            $img = "";
            $img = Image::make($image);
            //$img->encode('png', 100)->trim($img->pickColor(10, 10, 'hex'));
            //$img->trim('transparent', array('top', 'bottom'));
            $img->resize($sizes[0], $sizes[1], function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $img->stream();
            \Storage::disk('public')->put($imagePath . '/' . $resizeImgName, $img);

        }

    }

    /**
     * For uploading images
     *
     * @param String $image image object
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function uploadImage($image, $imagePath, $resizeArr)
    {
        $image_new = time() . rand() . '.' . $image->getClientOriginalExtension();
        $path = \Storage::disk('public')->putFileAs(
            $imagePath, $image, $image_new
        );

        /*
        call image resize function
         */
        if($image->getClientOriginalExtension() != 'pdf')
        $this->ImageResize($image, $image_new, $resizeArr, $imagePath);

        return $image_new;
    }

    /**
     * For uploading images from link
     *
     * @param String $image image object
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function uploadImageFromURL($image, $imagePath, $resizeArr)
    {
        $image_new = time() . rand() . '.'. pathinfo($image, PATHINFO_EXTENSION);
        $img = Image::make($image);
            
        $img->stream();
        \Storage::disk('public')->put($imagePath . '/' . $image_new, $img);

        $this->ImageResize($image, $image_new, $resizeArr, $imagePath);

        return $image_new;
    }

    /**
     * For uploading docs
     *
     * @param String $image image object
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function uploadDocs($image, $imagePath)
    {
        $image_new = time() . rand() . '.' . $image->getClientOriginalExtension();
        $path = \Storage::disk('public')->putFileAs(
            $imagePath, $image, $image_new
        );

        return $image_new;
    }

    /**
     * For uploading images with fix name
     *
     * @param String $image image object
     * @param String $imagePath image path
     * @param Array resizeArr image sizes
     */
    public function uploadImageWithSameName($image, $imagePath, $resizeArr)
    {

        $image_new = 'instagram.png';
        $path = \Storage::disk('public')->putFileAs(
            $imagePath, $image, $image_new
        );

        /*
        call image resize function
         */
        $this->ImageResize($image, $image_new, $resizeArr, $imagePath);

        return $image_new;
    }

    /**
     * For uploading videos
     *
     * @param String $video video object
     * @param String $videoPath video path
     */
    public function uploadVideo($video, $videoPath)
    {

        $video_new = "";
        if ($video) {
            $timeString = time();
            $video_new = $timeString . '.' . $video->getClientOriginalExtension();
            $path = \Storage::disk('public')->putFileAs($videoPath, $video, $video_new);

            $media = \FFMpeg::open($path);

            $media->getFrameFromSeconds(2)
                ->export()
                ->toDisk('local')
                ->save($videoPath . '/img-' . $timeString . '.png');

        }
        return $video_new;
    }

    /**
     * For getting video duration
     *
     * @param String $vedioPath full path of video
     */
    public function getVideoDuration($videoPath)
    {

        $media = \FFMpeg::open($videoPath);
        $duration = gmdate("H:i:s", $media->getDurationInSeconds());

        return $duration;
    }

    /**
     * For getting video thumbnail name
     *
     * @param String $video video name object
     */
    public function getVideoImage($video)
    {
        if ($video != "") {
            $videoNameArr = explode('.', $video);
            return 'img-' . $videoNameArr[0] . '.png';
        }
    }

    public function deleteUserData($user_id)
    {
        $rooms = Rooms::where('user_id', $user_id)->get();
        foreach($rooms as $room){
            $_room_id = $room->id;
            $rounds = Round::where('room_id', $_room_id)->pluck('id')->toArray();
            MatchRound::whereIn('round_id', $rounds)->forceDelete();
            Prediction::where('room_id', $_room_id)->forceDelete();
            RoomRuleSet::where('room_id', $_room_id)->forceDelete();
            Round::where('room_id', $_room_id)->forceDelete();
            BettingRoom::where('room_id', $_room_id)->forceDelete();
            $room->forceDelete();
        }
        BettingRoom::where('invited_id', $user_id)->forceDelete();
        LoginTrack::where('user_id', $user_id)->forceDelete();
        Prediction::where('user_id', $user_id)->forceDelete();
        RoomArchived::where('user_id', $user_id)->delete();
        UsersVerify::where('user_id', $user_id)->delete();
        DB::table('user_invitations')->where('user_id', $user_id)->forceDelete();
    }

}
