<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>New Request | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display",
                         "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f7;
            color: #1d1d1f;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f5f5f7;
        }

        /* Main */

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 72px 32px 100px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            color: #0071e3;
            text-decoration: none;
            font-size: 15px;

            margin-bottom: 36px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .eyebrow {
            color: #6e6e73;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 52px;
            line-height: 1.05;
            letter-spacing: -2px;
            margin: 0 0 16px;
            font-weight: 700;
        }

        .subtitle {
            color: #6e6e73;
            font-size: 20px;
            line-height: 1.5;
            margin: 0 0 46px;
            max-width: 650px;
        }

        /* Form card */

        .form-card {
            background: white;
            border-radius: 28px;
            padding: 42px;

            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.02),
                0 8px 30px rgba(0, 0, 0, 0.04);
        }

        .form-section {
            margin-bottom: 34px;
        }

        .form-section:last-of-type {
            margin-bottom: 0;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 9px;
            color: #1d1d1f;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #d2d2d7;
            border-radius: 12px;

            background: #fff;

            padding: 14px 15px;

            font-family: inherit;
            font-size: 16px;
            color: #1d1d1f;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 125, 250, 0.12);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.5;
        }

        .helper {
            margin-top: 8px;
            color: #86868b;
            font-size: 13px;
        }

        /* Priority */

        .priority-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .priority-option input {
            display: none;
        }

        .priority-option label {
            margin: 0;
            padding: 13px 10px;

            border: 1px solid #d2d2d7;
            border-radius: 12px;

            text-align: center;
            cursor: pointer;

            font-weight: 500;
            transition: all 0.2s ease;
        }

        .priority-option label:hover {
            background: #f5f5f7;
        }

        .priority-option input:checked + label {
            border-color: #0071e3;
            background: #eef7ff;
            color: #0071e3;
            box-shadow: 0 0 0 1px #0071e3;
        }

        /* Errors */

        .errors {
            background: #fff2f2;
            border: 1px solid #ffd2d2;
            border-radius: 14px;

            padding: 18px 20px;
            margin-bottom: 28px;

            color: #b42318;
            font-size: 14px;
        }

        .errors strong {
            display: block;
            margin-bottom: 7px;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Buttons */

        .actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;

            margin-top: 40px;
            padding-top: 28px;

            border-top: 1px solid #e5e5e7;
        }

        .cancel-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 44px;

            padding: 0 22px;

            border-radius: 980px;
            border: 1px solid #d2d2d7;

            color: #1d1d1f;
            background: white;

            text-decoration: none;
            font-size: 15px;
            font-weight: 500;

            transition: background 0.2s ease;
        }

        .cancel-button:hover {
            background: #f5f5f7;
        }

        .submit-button {
            min-height: 44px;

            border: none;
            border-radius: 980px;

            padding: 0 24px;

            background: #0071e3;
            color: white;

            font-family: inherit;
            font-size: 15px;
            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.1s ease;
        }

        .submit-button:hover {
            background: #0077ed;
        }

        .submit-button:active {
            transform: scale(0.98);
        }

        @media (max-width: 700px) {
            .page {
                padding: 48px 20px 70px;
            }

            h1 {
                font-size: 40px;
            }

            .form-card {
                padding: 26px 20px;
                border-radius: 22px;
            }

            .priority-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>

<x-fixflow-nav />

<main class="page">

    <a href="{{ route('maintenance.index') }}" class="back-link">
        ← Back to requests
    </a>

    <div class="eyebrow">FixFlow</div>

    <h1>New maintenance request.</h1>

    <p class="subtitle">
        Add an issue, choose the affected unit, and set its priority.
    </p>

    @if ($errors->any())
        <div class="errors">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">

        <form
            action="{{ route('maintenance.store') }}"
            method="POST"
        >
            @csrf

            <div class="form-section">
                <label for="unit_id">
                    Property & Unit
                </label>

                <select
                    id="unit_id"
                    name="unit_id"
                    required
                >
                    <option value="">
                        Select a unit
                    </option>

                    @foreach ($properties as $property)
                        @foreach ($property->units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                {{ old('unit_id') == $unit->id ? 'selected' : '' }}
                            >
                                {{ $property->name }}
                                — Unit {{ $unit->unit_number }}
                                — Floor {{ $unit->floor }}
                            </option>
                        @endforeach
                    @endforeach
                </select>

                <div class="helper">
                    The floor is automatically connected to the selected unit.
                </div>
            </div>

            <div class="form-section">
                <label for="title">
                    Issue
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="e.g. Water heater leaking"
                    required
                >
            </div>

            <div class="form-section">
                <label for="description">
                    Description / Comments
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Describe what's happening..."
                    required
                >{{ old('description') }}</textarea>

                <div class="helper">
                    Add any details that could help diagnose or repair the issue.
                </div>
            </div>

            <div class="form-section">
                <label>
                    Priority
                </label>

                <div class="priority-options">

                    <div class="priority-option">
                        <input
                            type="radio"
                            id="priority-low"
                            name="priority"
                            value="low"
                            {{ old('priority') === 'low' ? 'checked' : '' }}
                        >
                        <label for="priority-low">
                            Low
                        </label>
                    </div>

                    <div class="priority-option">
                        <input
                            type="radio"
                            id="priority-medium"
                            name="priority"
                            value="medium"
                            {{ old('priority', 'medium') === 'medium' ? 'checked' : '' }}
                        >
                        <label for="priority-medium">
                            Medium
                        </label>
                    </div>

                    <div class="priority-option">
                        <input
                            type="radio"
                            id="priority-high"
                            name="priority"
                            value="high"
                            {{ old('priority') === 'high' ? 'checked' : '' }}
                        >
                        <label for="priority-high">
                            High
                        </label>
                    </div>

                    <div class="priority-option">
                        <input
                            type="radio"
                            id="priority-emergency"
                            name="priority"
                            value="emergency"
                            {{ old('priority') === 'emergency' ? 'checked' : '' }}
                        >
                        <label for="priority-emergency">
                            Emergency
                        </label>
                    </div>

                </div>
            </div>

            <div class="actions">

                <a
                    href="{{ route('maintenance.index') }}"
                    class="cancel-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="submit-button"
                >
                    Create Request
                </button>

            </div>

        </form>

    </div>

</main>

</body>
</html>