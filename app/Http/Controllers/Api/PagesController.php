<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Privacy;
use App\Models\Terms;
use App\Models\SocialLink;
use App\Models\ContactUs;
use App\Models\Video;
use App\Models\Review;
use App\Models\Gallery;



class PagesController extends Controller
{
    public function aboutUs()
    {
        $aboutUs = AboutUs::first();
        return response()->json($aboutUs);
    }

    public function categories()
    {
        $categories = Category::select('id', 'name', 'slug')
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully',
            'data' => $categories
        ]);
    }

    public function banner()
    {
        $banner = Banner::all();
        return response()->json($banner);
    }

    public function review()
    {
        $review = Review::all();
        return response()->json($review);
    }

    public function gallery()
    {
        $gallery = Gallery::all();
        return response()->json($gallery);
    }

    public function faq()
    {
        $faqs = Faq::latest()->get();
        
        return response()->json($faqs);
    }

    public function privacy()
    {
        $privacy = Privacy::first();
        return response()->json($privacy);
    }
    public function contactDetails()
    {
        $contact = ContactUs::first();
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

   public function video()
   {
    $videos = Video::latest()->take(3)->get();

    $videos->map(function ($video) {
        $video->video_url = asset('storage/' . $video->video);
        return $video;
    });

    return response()->json([
        'success' => true,
        'data' => $videos
    ]);
    }



    public function terms()
   {
    $terms = Terms::first();
    return response()->json($terms);
   }
   
   public function socialLinks()
   {
       $socialLinks = SocialLink::latest()->get();
       return response()->json($socialLinks);
   }

    

}