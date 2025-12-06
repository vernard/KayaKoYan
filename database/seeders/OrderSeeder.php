<?php

namespace Database\Seeders;

use App\Enums\ChatMessageType;
use App\Enums\ListingType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\ChatMessage;
use App\Models\Delivery;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private int $orderCounter = 1;

    public function run(): void
    {
        // Get users
        $customers = User::where('role', 'customer')
            ->get()
            ->keyBy('email');

        $workers = User::where('role', 'worker')->get()->keyBy('email');

        // Order 1: Pending Payment (Miguel → Maria - VA Service)
        $this->createOrder(
            customer: $customers['miguel.torres@example.com'],
            workerEmail: 'maria.santos@example.com',
            listingTitle: 'Virtual Assistant - 10 Hours',
            status: OrderStatus::PendingPayment,
            notes: 'I need help organizing my emails and calendar for the next week.'
        );

        // Order 2: Payment Submitted (Sofia → Juan - Logo Design)
        $order2 = $this->createOrder(
            customer: $customers['sofia.lim@example.com'],
            workerEmail: 'juan.delacruz@example.com',
            listingTitle: 'Professional Logo Design',
            status: OrderStatus::PaymentSubmitted,
            notes: 'Logo for my new bakery business called "Sweet Dreams Bakeshop"'
        );
        $this->createPayment($order2, PaymentStatus::Pending);
        $this->createChatMessages($order2, [
            ['sender' => 'customer', 'message' => 'Hi! I just submitted the payment. Looking forward to working with you!'],
            ['sender' => 'worker', 'message' => 'Thank you! I received your payment submission. Let me verify it and I\'ll get back to you shortly with some questions about your brand.'],
        ]);

        // Order 3: Payment Received (David → Anna - Video Editing)
        $order3 = $this->createOrder(
            customer: $customers['david.cruz@example.com'],
            workerEmail: 'anna.reyes@example.com',
            listingTitle: 'YouTube Video Editing',
            status: OrderStatus::PaymentReceived,
            notes: 'Need editing for my travel vlog to Palawan. Raw footage is about 45 minutes.'
        );
        $this->createPayment($order3, PaymentStatus::Verified);
        $this->createChatMessages($order3, [
            ['sender' => 'customer', 'message' => 'Payment sent! Here\'s my GDrive link to the raw footage.'],
            ['sender' => 'worker', 'message' => 'Got it! Payment verified. I\'ll start working on your video today.'],
            ['sender' => 'worker', 'message' => 'Just a quick question - do you have any music preference for the background?'],
        ]);

        // Order 4: In Progress (Grace → Carlos - Social Media Management)
        $order4 = $this->createOrder(
            customer: $customers['grace.tan@example.com'],
            workerEmail: 'carlos.garcia@example.com',
            listingTitle: 'Social Media Management - 1 Month',
            status: OrderStatus::InProgress,
            notes: 'Instagram and Facebook for my clothing boutique. Target audience is women 25-40.'
        );
        $this->createPayment($order4, PaymentStatus::Verified);
        $this->createChatMessages($order4, [
            ['sender' => 'customer', 'message' => 'Hi Carlos! Excited to start our collaboration.'],
            ['sender' => 'worker', 'message' => 'Same here, Grace! I\'ve reviewed your current social media presence. Here\'s my initial strategy...'],
            ['sender' => 'customer', 'message' => 'This looks great! I especially like the content calendar idea.'],
            ['sender' => 'worker', 'message' => 'Perfect! I\'ll start creating the content. Will share drafts before posting.'],
            ['sender' => 'worker', 'message' => 'Here are the first batch of posts for your review. Let me know your thoughts!'],
        ]);

        // Order 5: Delivered (Mark → Lisa - Blog Article)
        $order5 = $this->createOrder(
            customer: $customers['mark.villanueva@example.com'],
            workerEmail: 'lisa.mendoza@example.com',
            listingTitle: 'Blog Article Writing - 1000 Words',
            status: OrderStatus::Delivered,
            notes: 'Article about "Top 10 Investment Tips for Young Professionals"'
        );
        $this->createPayment($order5, PaymentStatus::Verified);
        $this->createChatMessages($order5, [
            ['sender' => 'customer', 'message' => 'Hi Lisa, I need an article about investment tips for millennials.'],
            ['sender' => 'worker', 'message' => 'Great topic! I\'ll research current trends and provide actionable advice.'],
            ['sender' => 'customer', 'message' => 'Perfect. Please make it beginner-friendly.'],
            ['sender' => 'worker', 'message' => 'Absolutely! I\'ll avoid jargon and explain concepts clearly.'],
            ['sender' => 'worker', 'message' => 'Draft is ready! Let me know if you need any revisions.'],
            ['sender' => 'customer', 'message' => 'Looks good so far! Can you add a section about cryptocurrency?'],
            ['sender' => 'worker', 'message' => 'Done! I\'ve added a balanced section on crypto investments.'],
            ['sender' => 'worker', 'message' => 'Here\'s the final article with all revisions. Delivering now!', 'type' => ChatMessageType::DeliveryNotice],
        ]);
        $this->createDelivery($order5, 'Here are your 3 blog articles as requested. Each article is SEO-optimized with proper headings and meta descriptions. Let me know if you need any revisions!');

        // Order 6: Completed - Digital Product (Miguel → Juan - Canva Templates)
        $order6 = $this->createOrder(
            customer: $customers['miguel.torres@example.com'],
            workerEmail: 'juan.delacruz@example.com',
            listingTitle: 'Canva Social Media Templates Bundle',
            status: OrderStatus::Completed,
            notes: null,
            completedAt: now()->subDays(5)
        );
        $this->createPayment($order6, PaymentStatus::Verified);
        $this->createChatMessages($order6, [
            ['sender' => 'customer', 'message' => 'Just purchased your template bundle! Excited to use them.'],
            ['sender' => 'worker', 'message' => 'Thanks for your purchase! The download link should be available now. Let me know if you have any questions!'],
            ['sender' => 'customer', 'message' => 'Downloaded successfully. These look amazing!'],
            ['sender' => 'worker', 'message' => 'Glad you like them! Feel free to reach out if you need help customizing.'],
        ]);

        // Order 7: Completed - Digital Product (Sofia → Anna - Premiere Presets)
        $order7 = $this->createOrder(
            customer: $customers['sofia.lim@example.com'],
            workerEmail: 'anna.reyes@example.com',
            listingTitle: 'Premiere Pro Presets Pack',
            status: OrderStatus::Completed,
            notes: null,
            completedAt: now()->subDays(3)
        );
        $this->createPayment($order7, PaymentStatus::Verified);
        $this->createChatMessages($order7, [
            ['sender' => 'customer', 'message' => 'Hi! Just got the presets. Quick question - are they compatible with Premiere 2023?'],
            ['sender' => 'worker', 'message' => 'Yes, they work with all Premiere Pro versions from 2020 onwards. Enjoy editing!'],
        ]);

        // Order 8: Completed - Service (David → Maria - Admin Support)
        $order8 = $this->createOrder(
            customer: $customers['david.cruz@example.com'],
            workerEmail: 'maria.santos@example.com',
            listingTitle: 'Admin Support Package',
            status: OrderStatus::Completed,
            notes: 'Need help with document organization and basic bookkeeping for my small business.',
            completedAt: now()->subDays(7)
        );
        $this->createPayment($order8, PaymentStatus::Verified);
        $this->createChatMessages($order8, [
            ['sender' => 'customer', 'message' => 'Hi Maria! I have a lot of receipts and invoices that need organizing.'],
            ['sender' => 'worker', 'message' => 'No problem! Please share access to your documents and I\'ll get started.'],
            ['sender' => 'customer', 'message' => 'Shared! The folder is quite messy, sorry about that.'],
            ['sender' => 'worker', 'message' => 'Don\'t worry, I\'ve seen worse! I\'ll create a proper filing system.'],
            ['sender' => 'worker', 'message' => 'Update: I\'ve organized all 2024 documents by month and category.'],
            ['sender' => 'customer', 'message' => 'Wow, this is amazing! So much easier to find things now.'],
            ['sender' => 'worker', 'message' => 'Happy to help! I also created a simple tracking spreadsheet for expenses.'],
            ['sender' => 'customer', 'message' => 'This is exactly what I needed. Thank you so much!'],
            ['sender' => 'worker', 'message' => 'You\'re welcome! Here\'s the summary of all tasks completed.', 'type' => ChatMessageType::DeliveryNotice],
            ['sender' => 'customer', 'message' => 'Everything looks perfect. Accepting the delivery now!'],
        ]);
        $this->createDelivery($order8, 'Admin tasks completed for this week. Attached is the summary report including: organized document folders, expense tracking spreadsheet, and recommended filing system going forward.');

        // Order 9: Completed - Digital Product (Grace → Lisa - SEO E-book)
        $order9 = $this->createOrder(
            customer: $customers['grace.tan@example.com'],
            workerEmail: 'lisa.mendoza@example.com',
            listingTitle: 'SEO E-book for Beginners',
            status: OrderStatus::Completed,
            notes: null,
            completedAt: now()->subDays(10)
        );
        $this->createPayment($order9, PaymentStatus::Verified);
        $this->createChatMessages($order9, [
            ['sender' => 'customer', 'message' => 'Thank you for the e-book! Very informative.'],
        ]);

        // Order 10: Cancelled (Mark → Carlos - Instagram Growth Guide)
        $order10 = $this->createOrder(
            customer: $customers['mark.villanueva@example.com'],
            workerEmail: 'carlos.garcia@example.com',
            listingTitle: 'Instagram Growth Guide E-book',
            status: OrderStatus::Cancelled,
            notes: 'Changed my mind about this purchase.'
        );
        $this->createChatMessages($order10, [
            ['sender' => 'customer', 'message' => 'Hi, I\'d like to cancel this order. Haven\'t paid yet. Sorry for the inconvenience.'],
        ]);
    }

    private function createOrder(
        User $customer,
        string $workerEmail,
        string $listingTitle,
        OrderStatus $status,
        ?string $notes = null,
        ?\Carbon\Carbon $completedAt = null
    ): Order {
        $worker = User::where('email', $workerEmail)->first();
        $listing = Listing::where('user_id', $worker->id)
            ->where('title', $listingTitle)
            ->first();

        $orderNumber = 'KKY-' . str_pad($this->orderCounter++, 3, '0', STR_PAD_LEFT);

        return Order::withoutEvents(function () use ($customer, $worker, $listing, $orderNumber, $status, $notes, $completedAt) {
            return Order::create([
                'order_number' => $orderNumber,
                'listing_id' => $listing->id,
                'customer_id' => $customer->id,
                'worker_id' => $worker->id,
                'quantity' => 1,
                'unit_price' => $listing->price,
                'total_price' => $listing->price,
                'status' => $status,
                'notes' => $notes,
                'delivered_at' => in_array($status, [OrderStatus::Delivered, OrderStatus::Completed]) ? now()->subDays(1) : null,
                'completed_at' => $completedAt,
            ]);
        });
    }

    private function createPayment(Order $order, PaymentStatus $status): Payment
    {
        return Payment::withoutEvents(function () use ($order, $status) {
            return Payment::create([
                'order_id' => $order->id,
                'method' => fake()->randomElement([PaymentMethod::GCash, PaymentMethod::BankTransfer]),
                'amount' => $order->total_price,
                'reference_number' => strtoupper(fake()->bothify('??##??##??')),
                'status' => $status,
                'verified_at' => $status === PaymentStatus::Verified ? now() : null,
            ]);
        });
    }

    private function createDelivery(Order $order, string $notes): Delivery
    {
        return Delivery::withoutEvents(function () use ($order, $notes) {
            return Delivery::create([
                'order_id' => $order->id,
                'notes' => $notes,
            ]);
        });
    }

    private function createChatMessages(Order $order, array $messages): void
    {
        $minutesAgo = count($messages) * 30;

        foreach ($messages as $msg) {
            $senderId = $msg['sender'] === 'customer' ? $order->customer_id : $order->worker_id;
            $type = $msg['type'] ?? ChatMessageType::Text;

            ChatMessage::create([
                'order_id' => $order->id,
                'sender_id' => $senderId,
                'message' => $msg['message'],
                'type' => $type,
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ]);

            $minutesAgo -= 30;
        }
    }
}
