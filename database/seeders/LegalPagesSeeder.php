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
                'description' => '<p><em>Last Updated: July 14, 2026</em></p>
<p>Welcome to <strong>Abhaya Vastra</strong> ("Company", "we", "our", "us"). These Terms and Conditions ("Terms") govern your access to and use of our website located at <strong>http://abhayavastra.store</strong> (the "Site") and any purchases made through the Site.</p>
<p>By accessing the Site or purchasing products from us, you agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, please do not use the Site.</p>

<hr/>

<h3><strong>1. Definitions & Interpretation</strong></h3>
<ul>
  <li><strong>"Customer"</strong> refers to the user browsing the Site or placing an order.</li>
  <li><strong>"Products"</strong> refers to the apparel, clothing items, and custom t-shirts sold on the Site.</li>
  <li><strong>"Custom Designs"</strong> refers to any text, images, or graphics created or uploaded by the user via our Custom Design Studio.</li>
</ul>

<h3><strong>2. Account Registration</strong></h3>
<p>To access certain features of the Site or place an order, you may need to create an account. You agree to provide accurate, current, and complete information during registration. You are responsible for safeguarding your password and for all activities that occur under your account.</p>

<h3><strong>3. Custom Design Studio & User Content</strong></h3>
<ul>
  <li>By uploading any image, logo, text, or graphic to our Custom Design Studio, you warrant that you own the rights to the content or have obtained all necessary licenses and permissions to use it.</li>
  <li>You agree not to upload any content that is offensive, defamatory, illegal, or infringes upon any third-party intellectual property rights (including copyrights and trademarks).</li>
  <li>Abhaya Vastra reserves the right to reject and cancel any customization orders that violate our content guidelines.</li>
</ul>

<h3><strong>4. Product Descriptions, Pricing, and Payments</strong></h3>
<ul>
  <li>We make every effort to display the colors and details of our apparel as accurately as possible. However, the actual colors you see will depend on your monitor, and we cannot guarantee that your monitor\'s display of any color will be accurate.</li>
  <li>All prices are listed in Indian Rupees (INR) and are subject to change without notice. Prices are inclusive of applicable taxes unless stated otherwise.</li>
  <li>Payments must be made through our authorized payment gateways (e.g. Razorpay). By providing payment information, you represent that you are authorized to use the chosen payment method.</li>
</ul>

<h3><strong>5. Shipping, Cancellation, and Returns</strong></h3>
<p>Orders are shipped to the address provided during checkout. Shipping times and charges are calculated at checkout. Cancellations are only permitted before the order has been processed or printed. Due to the personalized nature of customized t-shirts, returns are only accepted in cases of manufacturing defects or printing errors.</p>

<h3><strong>6. Limitation of Liability</strong></h3>
<p>In no event shall Abhaya Vastra, nor its directors, employees, or partners, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of the Site.</p>

<h3><strong>7. Governing Law & Dispute Resolution</strong></h3>
<p>These Terms shall be governed by and construed in accordance with the laws of India. Any disputes arising out of or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts located in <strong>Bengaluru, Karnataka, India</strong>.</p>

<h3><strong>8. Legal & Business Registrations</strong></h3>
<p><strong>Udyam Registration Number:</strong> UDYAM-KR-03-0713235</p>

<h3><strong>9. Contact Information</strong></h3>
<p>For any questions or clarifications regarding these Terms, please contact us at:</p>
<p><strong>Abhaya Vastra</strong><br/>
Email: <a href="mailto:info@abhayavastra.store">info@abhayavastra.store</a><br/>
Address: #52/A, 6th Cross, Attur Layout, Yelahanka New Town, Bengaluru, Karnataka – 560064, India</p>'
            ]
        );

        Privacy::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Privacy Policy',
                'description' => '<p><em>Last Updated: July 14, 2026</em></p>
<p>At <strong>Abhaya Vastra</strong>, accessible from <strong>http://abhayavastra.store</strong>, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by Abhaya Vastra and how we use it.</p>
<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us at <strong>info@abhayavastra.store</strong>.</p>

<hr/>

<h3><strong>1. Information We Collect</strong></h3>
<p>We collect several types of information for various purposes to provide and improve our service to you:</p>
<ul>
  <li><strong>Personal Identification Information:</strong> Name, email address, phone number, shipping address, and billing details when you register an account or place an order.</li>
  <li><strong>Design Assets:</strong> Any photos, logos, or texts you upload to the Custom Design Studio.</li>
  <li><strong>Log Files & Usage Data:</strong> IP addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and click counts to analyze trends and administer the Site.</li>
</ul>

<h3><strong>2. How We Use Your Information</strong></h3>
<p>We use the collected data for various purposes, including to:</p>
<ul>
  <li>Process, fulfill, and ship your orders.</li>
  <li>Provide, operate, and maintain our Site.</li>
  <li>Improve, personalize, and expand our Site features (including our Custom Design Studio canvas).</li>
  <li>Understand and analyze how you use our Site.</li>
  <li>Develop new products, services, features, and functionality.</li>
  <li>Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the website, and for marketing and promotional purposes.</li>
  <li>Send you emails and transaction alerts.</li>
  <li>Find and prevent fraud.</li>
</ul>

<h3><strong>3. Sharing and Disclosure of Information</strong></h3>
<p>We do not sell, trade, or rent your personal identification information to others. We may share your information with trusted third-party service providers who assist us in operating our website, conducting our business, or servicing you, such as:</p>
<ul>
  <li>Payment gateways (e.g., Razorpay) to process payments securely.</li>
  <li>Delivery and logistics partners to deliver your purchased garments.</li>
  <li>Cloud storage services (e.g., Google Cloud Storage) to host your custom design files.</li>
</ul>

<h3><strong>4. Data Security</strong></h3>
<p>The security of your data is important to us. We use industry-standard encryption protocols (like SSL/TLS) and secure database storage systems to protect your personal details, credentials, and designs. However, please remember that no method of transmission over the Internet, or method of electronic storage is 100% secure.</p>

<h3><strong>5. Cookies and Web Beacons</strong></h3>
<p>Like any other website, Abhaya Vastra uses \'cookies\'. These cookies are used to store information including visitors\' preferences, and the pages on the website that the visitor accessed or visited. The information is used to optimize the users\' experience by customizing our web page content based on visitors\' browser type and/or other information.</p>

<h3><strong>6. Your Data Protection Rights (GDPR/CCPA/Indian Digital Personal Data Protection Act)</strong></h3>
<p>We want to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
<ul>
  <li><strong>The right to access:</strong> You have the right to request copies of your personal data.</li>
  <li><strong>The right to rectification:</strong> You have the right to request that we correct any information you believe is inaccurate or incomplete.</li>
  <li><strong>The right to erasure:</strong> You have the right to request that we erase your personal data, under certain conditions.</li>
  <li><strong>The right to restrict or object to processing:</strong> You have the right to request that we restrict or object to the processing of your personal data, under certain conditions.</li>
</ul>

<h3><strong>7. Governing Law</strong></h3>
<p>This Privacy Policy is governed by the laws of India, including the Information Technology Act, 2000, and the Digital Personal Data Protection (DPDP) Act, 2023. Any disputes shall be subject to the courts of Bengaluru, Karnataka.</p>

<h3><strong>8. Contact Details & Grievance Redressal</strong></h3>
<p>If you have any questions or complaints about this Privacy Policy, please contact our Grievance Officer at:</p>
<p>Email: <a href="mailto:info@abhayavastra.store">info@abhayavastra.store</a><br/>
Address: Abhaya Vastra, #52/A, 6th Cross, Attur Layout, Yelahanka New Town, Bengaluru, Karnataka – 560064, India</p>'
            ]
        );
    }
}
