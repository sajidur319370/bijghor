<x-page :title="__('About')">
    @if (app()->getLocale() === 'bn')
        <p>বীজঘর বাংলাদেশের ঘরের বাগানিদের জন্য একটি ছোট অনলাইন বীজের দোকান। এখানে ছোট প্যাকেটে সবজি, ফুল, ফল আর ভেষজের বীজ পাওয়া যায়, যাতে বারান্দা, ছাদ বা উঠানে সহজে চাষ শুরু করা যায়।</p>

        <h2>প্রতিটি প্যাকেটে যা জানবেন</h2>
        <ul>
            <li>কখন বুনবেন (বোনার মৌসুম)</li>
            <li>অঙ্কুরোদগমের হার</li>
            <li>বোনার কত দিন পর ফসল তোলা যায়</li>
            <li>প্যাকেটে কতটুকু বীজ আছে</li>
        </ul>

        <h2>কীভাবে অর্ডার করবেন</h2>
        <p>পছন্দের বীজ কার্টে যোগ করুন, চেকআউটে নাম, মোবাইল নম্বর ও ঠিকানা দিন, তারপর বিকাশে পেমেন্ট করুন। কোনো অ্যাকাউন্ট খুলতে হয় না। অর্ডার নম্বর আর মোবাইল নম্বর দিয়ে যেকোনো সময় <a href="{{ route('track') }}">অর্ডার ট্র্যাক</a> করতে পারবেন।</p>

        <h2>এই ডেমো সম্পর্কে</h2>
        <p>সাইটটি এখন ডেমো হিসেবে চলছে। পেমেন্ট বিকাশের টেস্ট (স্যান্ডবক্স) বা ডেমো মোডে হয়, তাই কোনো আসল টাকা কাটা হয় না, আর এখানে দেওয়া অর্ডারগুলো পরীক্ষামূলক।</p>

        <p>প্রশ্ন থাকলে <a href="{{ route('contact') }}">যোগাযোগ</a> পাতা দেখুন।</p>
    @else
        <p>BijGhor (বীজঘর) is a small online seed shop for home gardeners in Bangladesh. We sell vegetable, flower, fruit and herb seeds in small packets, so it is easy to start growing on a balcony, a rooftop or in a yard.</p>

        <h2>What every packet tells you</h2>
        <ul>
            <li>When to sow it (sowing season)</li>
            <li>The germination rate</li>
            <li>How many days until harvest</li>
            <li>How much seed is in the packet</li>
        </ul>

        <h2>How ordering works</h2>
        <p>Add seeds to your cart, enter your name, mobile number and address at checkout, then pay with bKash. You don't need an account. You can <a href="{{ route('track') }}">track your order</a> at any time with your order number and mobile number.</p>

        <h2>About this demo</h2>
        <p>The site is currently running as a demo. Payments use bKash test (sandbox) or demo mode, so no real money is charged, and orders placed here are test orders.</p>

        <p>Questions? See the <a href="{{ route('contact') }}">contact</a> page.</p>
    @endif
</x-page>
