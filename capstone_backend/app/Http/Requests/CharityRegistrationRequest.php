<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CharityRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Organization Details
            'organization_name' => 'required|string|max:255',
            'legal_trading_name' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:100',
            'tax_id' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            
            // Primary Contact - Structured Fields
            'primary_first_name' => 'required|string|max:50|regex:/^[A-Za-zÑñ\s]+$/',
            'primary_middle_initial' => 'nullable|string|max:1|regex:/^[A-Za-zÑñ]$/',
            'primary_last_name' => 'required|string|max:50|regex:/^[A-Za-zÑñ\s]+$/',
            'primary_position' => 'nullable|string|max:100',
            'primary_email' => 'required|email|max:100',
            'primary_phone' => 'required|regex:/^(09|\+639)\d{9}$/',
            
            // Location Fields
            'street_address' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'province' => 'nullable|string|max:100',
            'region' => 'required|string|max:100',
            'full_address' => 'nullable|string|max:1000',
            
            // Category
            'nonprofit_category' => 'required|string|max:100',
            
            // Account Security
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'password_confirmation' => 'required',
            
            // Mission & Profile
            'mission_statement' => 'required|string|max:1000',
            'description' => 'required|string|max:2000',
            
            // Terms
            'accept_terms' => 'required|accepted',
            'confirm_truthfulness' => 'required|accepted',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'primary_first_name.required' => 'First name is required',
            'primary_first_name.regex' => 'First name must contain only letters',
            'primary_middle_initial.regex' => 'Middle initial must be a single letter',
            'primary_last_name.required' => 'Last name is required',
            'primary_last_name.regex' => 'Last name must contain only letters',
            'primary_email.required' => 'Email address is required',
            'primary_email.email' => 'Please enter a valid email address',
            'primary_phone.required' => 'Phone number is required',
            'primary_phone.regex' => 'Phone number must be in format 09XXXXXXXXX or +639XXXXXXXXX',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'primary_first_name' => 'first name',
            'primary_middle_initial' => 'middle initial',
            'primary_last_name' => 'last name',
            'primary_position' => 'position',
            'primary_email' => 'email',
            'primary_phone' => 'phone number',
        ];
    }
}
