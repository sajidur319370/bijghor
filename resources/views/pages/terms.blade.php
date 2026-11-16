<x-page :title="__('Terms')" :updated="true">
    @if (app()->getLocale() === 'bn')
        <p>বীজঘর থেকে অর্ডার করার আগে শর্তগুলো পড়ে নিন। অর্ডার করলে ধরে নেওয়া হবে আপনি এগুলোতে রাজি।</p>

        <h2>অর্ডার ও দাম</h2>
        <ul>
            <li>সব দাম বাংলাদেশি টাকায় (৳)।</li>
            <li>ডেলিভারি চার্জ আপনার এলাকা অনুযায়ী চেকআউটে যোগ হয় এবং অর্ডারের আগেই দেখানো হয়।</li>
            <li>দাম পরে বদলাতে পারে, তবে আপনার অর্ডার যে দামে হয়েছে সেটিই বহাল থাকবে।</li>
        </ul>

        <h2>স্টক ও অপরিশোধিত অর্ডার</h2>
        <p>অর্ডার দেওয়ার সঙ্গে সঙ্গে বীজ আপনার জন্য রাখা হয়। ৬০ মিনিটের মধ্যে পেমেন্ট না হলে অর্ডার নিজে থেকেই বাতিল হয় এবং বীজ আবার বিক্রির জন্য খোলা হয়। একটি মোবাইল নম্বরে একসঙ্গে সর্বোচ্চ ৩টি অপরিশোধিত অর্ডার রাখা যায়।</p>

        <h2>পেমেন্ট</h2>
        <p>পেমেন্ট বিকাশের মাধ্যমে হয়। এই ডেমোতে বিকাশ টেস্ট (স্যান্ডবক্স) বা ডেমো মোড চালু, তাই কোনো আসল টাকা কাটা হয় না। আমরা কখনো আপনার বিকাশ পিন বা ওটিপি চাই না।</p>

        <h2>ডেলিভারি</h2>
        <p>অর্ডার আপনার দেওয়া ঠিকানায় পাঠানো হয়। প্রয়োজনে আমরা আপনার মোবাইল নম্বরে যোগাযোগ করি। ঠিকানা বা নম্বর ভুল হলে ডেলিভারি দেরি হতে পারে।</p>

        <h2>বীজ ও চাষ</h2>
        <p>অঙ্কুরোদগমের হার ভালো পরিবেশে মাপা হয়। মাটি, আবহাওয়া, পানি আর যত্নের ওপর ফল নির্ভর করে, তাই এটি নিশ্চয়তা নয়। বোনার মৌসুম মেনে বুনলে সবচেয়ে ভালো ফল পাবেন।</p>

        <h2>বাতিল ও ফেরত</h2>
        <p>স্টক শেষ হলে বা অর্ডারের তথ্য ভুল হলে আমরা অর্ডার বাতিল করতে পারি। পরিশোধিত অর্ডার বাতিল হলে টাকা ফেরত দেওয়া হয়। বিস্তারিত দেখুন <a href="{{ route('refund-policy') }}">ফেরত নীতি</a>।</p>

        <h2>শর্তের পরিবর্তন</h2>
        <p>শর্ত সময়ে সময়ে হালনাগাদ হতে পারে। নতুন শর্ত এই পাতায় তারিখসহ দেওয়া হবে।</p>
    @else
        <p>Please read these terms before ordering from BijGhor. By placing an order you agree to them.</p>

        <h2>Orders and prices</h2>
        <ul>
            <li>All prices are in Bangladeshi taka (৳).</li>
            <li>The delivery charge depends on your area. It is added at checkout and shown before you order.</li>
            <li>Prices may change later, but your order keeps the price it was placed at.</li>
        </ul>

        <h2>Stock and unpaid orders</h2>
        <p>Seeds are reserved for you as soon as you place an order. If payment is not made within 60 minutes, the order is cancelled automatically and the seeds go back on sale. One mobile number can have at most 3 unpaid orders at a time.</p>

        <h2>Payment</h2>
        <p>Payment is made with bKash. In this demo, bKash test (sandbox) or demo mode is on, so no real money is charged. We never ask for your bKash PIN or OTP.</p>

        <h2>Delivery</h2>
        <p>Orders are sent to the address you give. We contact you on your mobile number if needed. A wrong address or number can delay delivery.</p>

        <h2>Seeds and growing</h2>
        <p>Germination rates are measured in good conditions. Results depend on soil, weather, water and care, so a rate is not a guarantee. Sowing in the listed season gives the best results.</p>

        <h2>Cancellations and refunds</h2>
        <p>We may cancel an order if stock runs out or the order details are wrong. If a paid order is cancelled, the money is refunded. See the <a href="{{ route('refund-policy') }}">refund policy</a> for details.</p>

        <h2>Changes to these terms</h2>
        <p>These terms may be updated from time to time. The new version will be posted on this page with its date.</p>
    @endif
</x-page>
