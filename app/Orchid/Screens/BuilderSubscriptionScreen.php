<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;

class BuilderSubscriptionScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        // Dummy data for FR-B2B-01 (Plans) and FR-B2B-03 (Invoices)
        return [
            'plan' => new Repository([
                'tier' => 'Gold Partner',
                'status' => 'Active',
                'price' => '₹49,999 / month',
                'renewal' => '14 Jan 2026', // Approaching renewal
                'listings_limit' => 'Unlimited',
                'support' => 'Dedicated Manager',
            ]),

            'invoices' => [
                new Repository(['id' => 'INV-2025-012', 'date' => '14 Dec 2025', 'amount' => '₹49,999', 'status' => 'Paid']),
                new Repository(['id' => 'INV-2025-011', 'date' => '14 Nov 2025', 'amount' => '₹49,999', 'status' => 'Paid']),
                new Repository(['id' => 'INV-2025-010', 'date' => '14 Oct 2025', 'amount' => '₹49,999', 'status' => 'Paid']),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Subscription & Billing';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Manage your membership tier and billing history.';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            // Action to change plan (FR-B2B-01)
            Link::make('Upgrade Plan')
                ->icon('rocket')
                ->type(Color::WARNING), // Amber for upsell/urgency

            Button::make('Download Statement')
                ->icon('cloud-download')
                ->method('downloadStatement'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            // Section 1: Current Plan Details (Legend)
            Layout::legend('plan', [
                \Orchid\Screen\Sight::make('tier', 'Current Tier')
                    ->render(fn ($plan) => "<strong class='text-primary'>{$plan['tier']}</strong>"),
                
                \Orchid\Screen\Sight::make('status', 'Status')
                    ->render(fn() => "<span class='badge bg-success'>Active</span>"),

                \Orchid\Screen\Sight::make('price', 'Billing Amount'),
                \Orchid\Screen\Sight::make('renewal', 'Next Renewal'),
                \Orchid\Screen\Sight::make('listings_limit', 'Project Listings'),
                \Orchid\Screen\Sight::make('support', 'Support Level'),
            ])->title('Plan Overview'),

            // Section 2: Billing History Table (FR-B2B-03)
            Layout::table('invoices', [
                TD::make('id', 'Invoice #'),
                TD::make('date', 'Billing Date'),
                TD::make('amount', 'Amount'),
                TD::make('status', 'Status')
                    ->render(fn() => "<span class='text-success'>● Paid</span>"),
                
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn () => Button::make('PDF')
                        ->icon('doc')
                        ->type(Color::LIGHT)),
            ])->title('Billing History'),
        ];
    }

    /**
     * Logic for downloading statements
     */
    public function downloadStatement()
    {
        Toast::success('Statement downloaded successfully.');
    }
}