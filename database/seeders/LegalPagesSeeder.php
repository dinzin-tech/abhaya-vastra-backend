<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Terms;
use App\Models\Privacy;

class LegalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Terms::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Terms and Conditions',
                'description' => '<h2><strong>1. Introduction</strong></h2>
<p>Welcome to Abhaya Vastra. These Terms and Conditions govern your use of our website located at http://localhost:3000 and the purchase of any apparel or custom designed products from us. By accessing our website, you agree to comply with and be bound by these terms.</p>

<h2><strong>2. Intellectual Property Rights</strong></h2>
<p>Unless otherwise stated, Abhaya Vastra owns the intellectual property rights for all material and designs on this website. You may browse and order products, but you must not copy, reproduce, or redistribute any of our print designs without our explicit written permission.</p>

<h2><strong>3. Custom Design Studio Guidelines</strong></h2>
<p>When using our Custom Design Studio to customize apparel, you are solely responsible for any content or designs you upload. You warrant that you have the right to use any images uploaded and that they do not infringe upon any third-party copyrights or trademarks.</p>

<h2><strong>4. User Accounts and Registration</strong></h2>
<p>To place orders, you may be required to register an account. You must maintain the confidentiality of your account credentials and are responsible for all activities occurring under your account.</p>

<h2><strong>5. Limitation of Liability</strong></h2>
<p>Abhaya Vastra, registered under Udyam Number UDYAM-KR-03-0713235, will not be liable for any indirect, consequential, or special liability arising out of or in connection with your use of this website.</p>

<h2><strong>6. Governing Law</strong></h2>
<p>These terms will be governed by and interpreted in accordance with the laws of Karnataka, India, and you submit to the non-exclusive jurisdiction of the state and federal courts located in Bengaluru for the resolution of any disputes.</p>

<h2><strong>7. Contact Us</strong></h2>
<p>If you have any questions about these Terms, please contact us at info@abhayavastra.store.</p>'
            ]
        );

        Privacy::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Privacy Policy',
                'description' => '<h2><strong>1. Information We Collect</strong></h2>
<p>We collect personal information that you provide directly to us when placing an order, registering an account, or interacting with our Custom Design Studio. This includes your name, shipping address, email address, phone number, and any uploaded design assets.</p>

<h2><strong>2. How We Use Your Information</strong></h2>
<p>We use your information to process and deliver your orders, communicate with you regarding order status, improve our custom design tools, and send promotional updates (if opted in).</p>

<h2><strong>3. Sharing Your Information</strong></h2>
<p>We do not sell or trade your personal information. We may share your data with trusted third-party services like payment gateways (e.g. Razorpay) and logistics partners solely to facilitate your transaction and delivery.</p>

<h2><strong>4. Data Security</strong></h2>
<p>We implement a variety of security measures, including database encryption for sensitive credentials and secure transmission protocols, to maintain the safety of your personal information.</p>

<h2><strong>5. Cookies and Tracking</strong></h2>
<p>Our website uses cookies to enhance your browsing experience, remember items in your cart, and track general site traffic analytics.</p>

<h2><strong>6. Your Rights</strong></h2>
<p>You have the right to request access to the personal data we hold about you, request corrections, or request deletion of your account and associated information.</p>

<h2><strong>7. Changes to This Policy</strong></h2>
<p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated revision date.</p>

<h2><strong>8. Contact Details</strong></h2>
<p>For any inquiries regarding this Privacy Policy, please contact us at info@abhayavastra.store.</p>'
            ]
        );
    }
}
