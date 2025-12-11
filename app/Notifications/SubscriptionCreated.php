<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Subscription;

class SubscriptionCreated extends Notification
{
    use Queueable;

    protected $subscription;

    
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    
    public function via($notifiable)
    {
        return ['mail'];
    }

    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subscription Activated')
            ->line('Your subscription to ' . $this->subscription->product->title . ' has been activated.')
            ->line('Amount: ' . $this->subscription->product->price)
            ->line('Expires: ' . $this->subscription->expires_at->format('Y-m-d'))
            ->action('View Subscription', url('/subscriptions/' . $this->subscription->id))
            ->line('Thank you for your subscription!');
    }

    
    public function toArray($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
            'product_title' => $this->subscription->product->title,
            'amount' => $this->subscription->product->price,
            'expires_at' => $this->subscription->expires_at->format('Y-m-d'),
        ];
    }
}
