
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit {{ $property->name }} | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f5f7;
            color: #1d1d1f;
            font-family: -apple-system, BlinkMacSystemFont,
                "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: #0071e3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 54px 24px 100px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 500;
        }

        .eyebrow {
            color: #0071e3;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: clamp(38px, 6vw, 58px);
            letter-spacing: -2px;
            line-height: 1.1;
            margin: 0 0 16px;
        }

        .subtitle {
            color: #6e6e73;
            font-size: 17px;
            line-height: 1.6;
            margin: 0 0 36px;
            max-width: 600px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e8e8ed;
            border-radius: 24px;
            padding: 36px;
            margin-bottom: 22px;
        }

        .card-heading {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 0 0 10px;
        }

        .card-description {
            font-size: 14px;
            color: #6e6e73;
            line-height: 1.6;
            margin: 0 0 28px;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f5f5f7;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .company-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            border-radius: 14px;
            background: #e8f2ff;
            color: #0071e3;
            font-size: 23px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .company-hint {
            color: #6e6e73;
            font-size: 13px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 24px;
        }

        .field:last-child {
            margin-bottom: 0;
        }

        .field label {
            display: block;
            font-size: 14px;
            font-weight: 650;
            margin-bottom: 10px;
        }

        .required {
            color: #d92d20;
        }

        .field input {
            display: block;
            width: 100%;
            min-height: 50px;
            padding: 13px 16px;
            border: 1px solid #d2d2d7;
            border-radius: 13px;
            background: #ffffff;
            color: #1d1d1f;
            font: inherit;
            font-size: 15px;
            transition: border-color .2s, box-shadow .2s;
        }

        .field input:focus {
            outline: none;
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .12);
        }

        .field input::placeholder {
            color: #a1a1a6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 18px;
        }

        .error-box {
            padding: 18px 20px;
            background: #fff0f0;
            border: 1px solid #ffd5d5;
            border-radius: 14px;
            color: #b42318;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .error-box strong {
            display: block;
            margin-bottom: 8px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 20px;
            line-height: 1.8;
        }

        .field-error {
            margin-top: 8px;
            color: #d92d20;
            font-size: 13px;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 12px 24px;
            border: none;
            border-radius: 999px;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .button-primary {
            background: #0071e3;
            color: #ffffff;
        }

        .button-primary:hover {
            background: #0077ed;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .button-secondary {
            background: #e8e8ed;
            color: #1d1d1f;
        }

        .button-secondary:hover {
            background: #dcdce1;
            text-decoration: none;
        }

        .footer-note {
            text-align: center;
            color: #86868b;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 30px;
        }

        @media (max-width: 700px) {
            .page {
                padding: 36px 16px 70px;
            }

            .card {
                padding: 24px;
                border-radius: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <x-fixflow-nav />

    <main class="page">

        <a
            href="{{ route('properties.show', $property) }}"
            class="back-link"
        >
            ← Back to {{ $property->name }}
        </a>

        <div class="eyebrow">
            Property Management
        </div>

        <h1>Edit property.</h1>

        <p class="subtitle">
            Update the information for {{ $property->name }}.
            Your existing units and maintenance history
            will remain unchanged.
        </p>

        @if ($errors->any())
            <div class="error-box" role="alert">
                <strong>
                    Please correct the following:
                </strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('properties.update', $property) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <section class="card">

                <h2 class="card-heading">
                    Property information
                </h2>

                <p class="card-description">
                    Update the name of this property.
                    Company ownership cannot be changed here.
                </p>

                <div class="company-info">

                    <div class="company-icon" aria-hidden="true">
                        &#127970;
                    </div>

                    <div>
                        <div class="company-name">
                            {{ $property->company?->name ?? 'Property management' }}
                        </div>

                        <div class="company-hint">
                            Managing company · Cannot be changed
                        </div>
                    </div>

                </div>

                <div class="field">

                    <label for="name">
                        Property name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $property->name) }}"
                        placeholder="e.g. Harbor Point"
                        maxlength="255"
                        required
                    >

                    @error('name')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </section>

            <section class="card">

                <h2 class="card-heading">
                    Property address
                </h2>

                <p class="card-description">
                    Update the property's address so your
                    team can identify its location accurately.
                </p>

                <div class="field">

                    <label for="address">
                        Street address
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="address"
                        id="address"
                        value="{{ old('address', $property->address) }}"
                        placeholder="e.g. 100 Example Street"
                        maxlength="255"
                        required
                    >

                    @error('address')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="form-grid">

                    <div class="field">

                        <label for="city">
                            City
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="city"
                            id="city"
                            value="{{ old('city', $property->city) }}"
                            placeholder="Stamford"
                            maxlength="255"
                            required
                        >

                        @error('city')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label for="state">
                            State
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="state"
                            id="state"
                            value="{{ old('state', $property->state) }}"
                            placeholder="CT"
                            maxlength="100"
                            required
                        >

                        @error('state')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label for="zip_code">
                            ZIP code
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="zip_code"
                            id="zip_code"
                            value="{{ old('zip_code', $property->zip_code) }}"
                            placeholder="06902"
                            maxlength="20"
                            required
                        >

                        @error('zip_code')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </section>

            <div class="actions">

                <a
                    href="{{ route('properties.show', $property) }}"
                    class="button button-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    Save Changes →
                </button>

            </div>

        </form>

        <p class="footer-note">
            Property updates are restricted to authorized
            administrators and managers.
        </p>

    </main>

</body>
</html>
