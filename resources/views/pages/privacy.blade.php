<x-page :title="__('Privacy')" :updated="true">
    @if (app()->getLocale() === 'bn')
        <h2>আমরা কী তথ্য রাখি</h2>
        <ul>
            <li>অর্ডারের সময় দেওয়া নাম, মোবাইল নম্বর, ইমেইল (ঐচ্ছিক), জেলা, ঠিকানা ও নোট</li>
            <li>কী কিনেছেন, দাম ও অর্ডারের অবস্থা</li>
            <li>পেমেন্টের তথ্য: বিকাশ ট্রানজ্যাকশন আইডি, পরিমাণ ও যে নম্বর থেকে পেমেন্ট হয়েছে। আপনার বিকাশ পিন বা ওটিপি আমরা কখনো দেখি না বা রাখি না।</li>
        </ul>

        <h2>কেন রাখি</h2>
        <p>শুধু অর্ডার পৌঁছে দিতে, অর্ডার নিয়ে আপনার সঙ্গে যোগাযোগ করতে এবং আপনাকে অর্ডার ট্র্যাক করতে দিতে।</p>

        <h2>কুকি</h2>
        <p>কার্ট আর বেছে নেওয়া ভাষা মনে রাখতে একটি সেশন কুকি এবং ফর্ম সুরক্ষার জন্য একটি নিরাপত্তা টোকেন ব্যবহার হয়। কোনো বিজ্ঞাপন বা ট্র্যাকিং কুকি নেই। ফন্টও আমাদের নিজের সার্ভার থেকে আসে।</p>

        <h2>কার সঙ্গে শেয়ার করি</h2>
        <p>আপনার তথ্য আমরা বিক্রি করি না। পেমেন্টের সময় বিকাশ আপনার বিকাশ নম্বর ও টাকার পরিমাণ পায়। অর্ডার পাঠানোর জন্য ডেলিভারি সেবাকে নাম, নম্বর ও ঠিকানা দেওয়া হয়। আইন অনুযায়ী বাধ্য হলে কর্তৃপক্ষকে দেওয়া হতে পারে।</p>

        <h2>কত দিন রাখি</h2>
        <p>হিসাবের প্রয়োজনে অর্ডারের রেকর্ড রাখা হয়। যে তথ্য আর দরকার নেই তা মুছে দিতে বলতে পারেন।</p>

        <h2>আপনার অধিকার</h2>
        <p>আপনার তথ্য দেখতে, ঠিক করতে বা মুছে দিতে <a href="{{ route('contact') }}">যোগাযোগ</a> করুন।</p>
    @else
        <h2>What we keep</h2>
        <ul>
            <li>The name, mobile number, email (optional), district, address and note you give at checkout</li>
            <li>What you bought, the prices and the order status</li>
            <li>Payment details: the bKash transaction ID, the amount and the number that paid. We never see or store your bKash PIN or OTP.</li>
        </ul>

        <h2>Why we keep it</h2>
        <p>Only to deliver your order, contact you about it, and let you track it.</p>

        <h2>Cookies</h2>
        <p>We use one session cookie to remember your cart and chosen language, and a security token that protects forms. There are no advertising or tracking cookies. Our fonts are served from our own server too.</p>

        <h2>Who we share it with</h2>
        <p>We do not sell your information. When you pay, bKash receives your bKash number and the amount. To send your order, the delivery service gets your name, number and address. We may share information with authorities when the law requires it.</p>

        <h2>How long we keep it</h2>
        <p>Order records are kept for accounting. You can ask us to delete details that are no longer needed.</p>

        <h2>Your rights</h2>
        <p><a href="{{ route('contact') }}">Contact us</a> to see, correct or delete your information.</p>
    @endif
</x-page>
