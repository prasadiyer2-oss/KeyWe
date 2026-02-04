<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class BuilderProfileScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        // Dummy data for existing profile
        return [
            'company' => [
                'name' => 'Green Valley Developers Pvt Ltd',
                'rera' => 'P51800001234',
                'website' => 'https://www.greenvalley.com',
                'description' => 'Building sustainable homes in Navi Mumbai since 2010.',
                'address' => '101, Tech Park, Vashi, Navi Mumbai',
                'contact_email' => 'sales@greenvalley.com',
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Company Profile';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Manage company details and verification documents (KYC).';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save Changes')
                ->icon('check')
                ->method('save')
                ->type(Color::SUCCESS), // KeyWe Green
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            // Section 1: Brand Identity (FR-D-04)
            Layout::rows([
                Group::make([
                    Input::make('company.name')
                        ->title('Company Name')
                        ->placeholder('Registered Company Name')
                        ->required(),

                    Input::make('company.rera')
                        ->title('RERA Registration ID')
                        ->placeholder('e.g. P518000XXXXX')
                        ->help('Required for the "Verified" badge.'),
                ]),

                Input::make('company.website')
                    ->title('Website URL')
                    ->type('url'),

                TextArea::make('company.description')
                    ->title('About the Builder')
                    ->rows(4)
                    ->placeholder('Brief description for buyer visibility.'),

            ])->title('Brand Identity'),

            // Section 2: Contact Information
            Layout::rows([
                Group::make([
                    Input::make('company.contact_email')
                        ->title('Sales Email')
                        ->type('email'),
                    
                    Input::make('company.contact_phone')
                        ->title('Sales Phone')
                        ->mask('(999) 999-9999'), // Input mask for phone
                ]),

                Input::make('company.address')
                    ->title('Office Address')
                    ->placeholder('Full registered address'),

            ])->title('Contact Information'),

            // Section 3: Legal & KYC (FR-S-02)
            Layout::rows([
                Input::make('company.gst')
                    ->title('GST Number')
                    ->placeholder('GSTIN'),

                // In a real app, use Upload::make() here. 
                // For this UI demo, we use file input type.
                Group::make([
                    Input::make('docs.rera_cert')
                        ->type('file')
                        ->title('RERA Certificate')
                        ->help('Upload PDF (Max 2MB)'),

                    Input::make('docs.pan_card')
                        ->type('file')
                        ->title('Company PAN')
                        ->help('Upload PDF or JPG'),
                ]),
                
            ])->title('Legal Verification (KYC)'),
        ];
    }

    /**
     * Logic to save profile
     */
    public function save(Request $request)
    {
        Toast::success('Company profile updated. Verification documents submitted for review.');
    }
}