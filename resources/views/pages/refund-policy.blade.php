<x-page :title="__('Refund policy')" :updated="true">
    @if (app()->getLocale() === 'bn')
        <h2>ভুল বা ক্ষতিগ্রস্ত প্যাকেট</h2>
        <p>ভুল বীজ পেলে বা প্যাকেট ছেঁড়া/ক্ষতিগ্রস্ত পেলে ডেলিভারির ৭ দিনের মধ্যে অর্ডার নম্বর ও একটি ছবিসহ আমাদের জানান। আমরা প্যাকেট বদলে দেব অথবা টাকা ফেরত দেব।</p>

        <h2>খোলা প্যাকেট</h2>
        <p>বীজ জীবন্ত পণ্য, তাই খোলা প্যাকেট ফেরত নেওয়া হয় না।</p>

        <h2>অঙ্কুরোদগম</h2>
        <p>অঙ্কুরোদগম মাটি, আবহাওয়া ও যত্নের ওপর নির্ভর করে, তাই এর নিশ্চয়তা দেওয়া যায় না। মৌসুম আর নির্দেশনা মেনে বোনার পরও কোনো প্যাকেটের বীজ একেবারেই না গজালে আমাদের জানান, আমরা বিষয়টি দেখব।</p>

        <h2>বাতিল অর্ডার</h2>
        <p>পরিশোধিত অর্ডার বাতিল হলে পুরো টাকা যে বিকাশ নম্বর থেকে পেমেন্ট হয়েছিল সেখানে ফেরত দেওয়া হয়, সাধারণত ৭ কর্মদিবসের মধ্যে। অপরিশোধিত অর্ডার বাতিল হলে কিছু ফেরত দেওয়ার থাকে না।</p>

        <h2>ডেমো চলাকালীন</h2>
        <p>ডেমোতে কোনো আসল টাকা কাটা হয় না, তাই ফেরত দেওয়ার মতো কিছু নেই।</p>

        <p>ফেরতের জন্য <a href="{{ route('contact') }}">যোগাযোগ</a> করুন।</p>
    @else
        <h2>Wrong or damaged packets</h2>
        <p>If you receive the wrong seeds or a torn or damaged packet, tell us within 7 days of delivery with your order number and a photo. We will replace the packet or refund you.</p>

        <h2>Opened packets</h2>
        <p>Seeds are a living product, so opened packets cannot be returned.</p>

        <h2>Germination</h2>
        <p>Germination depends on soil, weather and care, so it cannot be guaranteed. If the seeds in a packet do not sprout at all even though you sowed them in season and as directed, let us know and we will look into it.</p>

        <h2>Cancelled orders</h2>
        <p>If a paid order is cancelled, the full amount is refunded to the bKash number that paid, usually within 7 working days. Nothing needs refunding when an unpaid order is cancelled.</p>

        <h2>During the demo</h2>
        <p>No real money is charged in the demo, so there is nothing to refund.</p>

        <p><a href="{{ route('contact') }}">Contact us</a> for a refund.</p>
    @endif
</x-page>
