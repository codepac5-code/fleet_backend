<x-master-layout>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('messages.driver_application')}}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --d-primary: #F8A609;
            --d-secondary: #312873;
            --d-light: #f8f9fa;
            --d-dark: #343a40;
            --d-gray: #6c757d;
            --d-success: #28a745;
            --d-danger: #dc3545;
            --d-bg-color: #f5f7fb;
            --d-card-bg: #ffffff;
            --d-text-color: #343a40;
            --d-border-color: #e0e0e0;
        }

        .body.dark-mode {
            --d-bg-color: #1a1a2e;
            --d-card-bg: #16213e;
            --d-text-color: #e0e0e0;
            --d-border-color: #2d4059;
            --d-light: #2d4059;
            --d-dark: #e0e0e0;
            --d-gray: #a0a0a0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .d-body {
            background-color: var(--d-bg-color);
            color: var(--d-text-color);
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .d-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .d-header {
            background: linear-gradient(135deg, var(--d-secondary), #1e1a5a);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .d-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .d-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .d-logo i {
            font-size: 28px;
            color: var(--d-primary);
        }

        .d-logo h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .d-status-badge {
            background-color: var(--d-primary);
            color: var(--d-secondary);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        .d-driver-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
        }

        .d-driver-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--d-primary);
            object-fit: cover;
        }

        .d-driver-details h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .d-driver-details p {
            color: #e0e0e0;
            font-size: 16px;
        }

        .d-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 25px;
        }

        .d-main-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .d-sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .d-card {
            background-color: var(--d-card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--d-border-color);
            transition: all 0.3s ease;
        }

        .d-card-header {
            background-color: var(--d-secondary);
            color: white;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .d-card-header h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .d-card-header i {
            transition: transform 0.3s ease;
        }

        .d-card-header.d-collapsed i {
            transform: rotate(180deg);
        }

        .d-card-body {
            padding: 20px;
        }

        .d-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .d-info-item {
            margin-bottom: 15px;
        }

        .d-info-label {
            font-weight: 600;
            color: var(--d-secondary);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .d-info-value {
            color: var(--d-text-color);
            font-size: 16px;
            padding: 8px 12px;
            background-color: var(--d-light);
            border-radius: 8px;
            border-left: 4px solid var(--d-primary);
        }

        .d-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .d-image-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            background-color: var(--d-card-bg);
        }

        .d-image-item:hover {
            transform: translateY(-5px);
        }

        .d-image-item img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }

        .d-image-caption {
            padding: 10px;
            background-color: var(--d-card-bg);
            text-align: center;
            font-size: 14px;
            color: var(--d-secondary);
            font-weight: 500;
        }

        .d-car-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .d-car-badge {
            background-color: var(--d-primary);
            color: var(--d-secondary);
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 18px;
        }

        .d-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .d-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .d-btn-primary {
            background-color: var(--d-primary);
            color: var(--d-secondary);
        }

        .d-btn-primary:hover {
            background-color: #e69500;
        }

        .d-btn-whatsapp {
            background-color: #25D366;
            color: white;
        }

        .d-btn-whatsapp:hover {
            background-color: #128C7E;
        }

        .d-btn-success {
            background-color: var(--d-success);
            color: white;
        }

        .d-btn-success:hover {
            background-color: #218838;
        }

        .d-btn-danger {
            background-color: var(--d-danger);
            color: white;
        }

        .d-btn-danger:hover {
            background-color: #c82333;
        }

        .d-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .d-modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .d-modal-content img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
        }

        .d-close-modal {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 30px;
            cursor: pointer;
        }

        .d-whatsapp-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }

        .d-whatsapp-modal-content {
            background-color: var(--d-card-bg);
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .d-whatsapp-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--d-border-color);
        }

        .d-whatsapp-modal-header h3 {
            color: var(--d-secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .d-whatsapp-modal-header h3 i {
            color: #25D366;
        }

        .d-close-whatsapp-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--d-gray);
            cursor: pointer;
            transition: color 0.3s;
        }

        .d-close-whatsapp-modal:hover {
            color: var(--d-danger);
        }

        .d-message-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .d-form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .d-form-group label {
            font-weight: 600;
            color: var(--d-secondary);
        }

        .d-form-group textarea {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--d-border-color);
            background-color: var(--d-light);
            color: var(--d-text-color);
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.3s;
        }

        .d-form-group textarea:focus {
            outline: none;
            border-color: var(--d-primary);
        }

        .d-message-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .d-btn-cancel {
            background-color: var(--d-gray);
            color: white;
        }

        .d-btn-cancel:hover {
            background-color: #5a6268;
        }

        @media (max-width: 992px) {
            .d-content-wrapper {
                grid-template-columns: 1fr;
            }

            .d-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .d-header-content {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .d-driver-info {
                flex-direction: column;
                text-align: center;
            }

            .d-images-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }









        .d-modal-image-container {
    overflow: auto;
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px;
    max-height: 70vh;
    cursor: grab;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    margin: 20px;
}

.d-modal-content img {
    max-width: none;
    max-height: none;
    transition: transform 0.3s ease;
    transform-origin: 0 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: 3px solid #fff;
    border-radius: 8px;
    background: white;
}

.d-modal-image-container:active {
    cursor: grabbing;
}

.d-modal-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.d-zoom-btn {
    padding: 12px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    transition: all 0.3s ease;
    min-width: 50px;
}

.d-zoom-btn:hover:not(:disabled) {
    background: #0056b3;
    transform: translateY(-2px);
}

.d-zoom-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.d-zoom-level {
    font-weight: bold;
    color: #495057;
    background: white;
    padding: 8px 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    min-width: 70px;
    text-align: center;
}

.d-modal-content {
    max-width: 98%;
    max-height: 98%;
    position: relative;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
}

.d-modal-caption {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    font-weight: bold;
    color: #333;
    font-size: 16px;
}

.d-close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 35px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1000;
    color: #333;
    background: rgba(255, 255, 255, 0.9);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.d-close-modal:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}








    </style>
</head>
<body class="d-body">
    <div class="d-container">
        <header class="d-header">
            <div class="d-header-content">
                <div class="d-logo">
                    <i class="fas fa-car-side"></i>
                    <h1 style="color: white;">{{ __('messages.driver_application') }}</h1>
                </div>
                <div class="d-status-badge">{{ __('messages.under_review') }}</div>
            </div>
            <div class="d-driver-info">
                <img src="{{ $application->profileImage }}" alt="{{ __('messages.driver_photo') }}" class="d-driver-avatar" id="dProfileImage">
                <div class="d-driver-details">
                    <h2 style="color: white;" >{{ $application->name }}</h2>
                    <p >{{ $application->phoneNumber}}</p>
                </div>
            </div>
        </header>

        <div class="d-content-wrapper">
            <div class="d-main-content">
                <!-- Vehicle Information -->
                <div class="d-card">
                    <div class="d-card-header">
                        <h3 style="color: white"><i class="fas fa-car"></i> {{ __('messages.vehicle_information') }}</h3>
                    </div>
                    <div class="d-card-body" id="dVehicleBody">
                        <div class="d-car-details">
                            <div class="d-car-badge">{{ $application->brand ." ".$application->model ." ".$application->year }}</div>
                            <div class="d-info-grid">
                                <div class="d-info-item">
                                    <div class="d-info-label">{{ __('messages.brand') }}</div>
                                    <div class="d-info-value" >{{ $application->brand}}</div>
                                </div>
                                <div class="d-info-item">
                                    <div class="d-info-label">{{ __('messages.model') }}</div>
                                    <div class="d-info-value">{{$application->model}}</div>
                                </div>
                                <div class="d-info-item">
                                    <div class="d-info-label">{{ __('messages.manufacturing_year') }}</div>
                                    <div class="d-info-value" >{{ $application->year}}</div>
                                </div>
                                <div class="d-info-item">
                                    <div class="d-info-label">{{ __('messages.color') }}</div>
                                    <div class="d-info-value" >{{$application->color}}</div>
                                </div>
                                <div class="d-info-item">
                                    <div class="d-info-label">{{ __('messages.plate_number') }}</div>
                                    <div class="d-info-value" >{{ $application->plateNumber}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Images -->
                <div class="d-card">
                    <div class="d-card-header" id="dCarImagesHeader">
                        <h3 style="color: white;"><i class="fas fa-images"></i> {{ __('messages.vehicle_images') }}</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="d-card-body" id="dCarImagesBody">
                        <div class="d-images-grid">

                        @if($application->frontCarImage)
                        <div class="d-image-item">
                            <img src="{{ $application->frontCarImage }}" alt="{{ __('messages.front_vehicle_image') }}">
                            <div class="d-image-caption">{{ __('messages.front') }}</div>
                        </div>
                        @endif

                        @if($application->backCarImage)
                        <div class="d-image-item">
                            <img src="{{ $application->backCarImage }}" alt="{{ __('messages.rear_vehicle_image') }}">
                            <div class="d-image-caption">{{ __('messages.rear') }}</div>
                        </div>
                        @endif

                        @if($application->rightCarImage)
                        <div class="d-image-item">
                            <img src="{{ $application->rightCarImage }}" alt="{{ __('messages.right_vehicle_image') }}">
                            <div class="d-image-caption">{{ __('messages.right') }}</div>
                        </div>
                        @endif

                        @if($application->leftCarImage)
                        <div class="d-image-item">
                            <img src="{{ $application->leftCarImage }}" alt="{{ __('messages.left_vehicle_image') }}">
                            <div class="d-image-caption">{{ __('messages.left') }}</div>
                        </div>
                        @endif

                        @if($application->insideCarImage)
                        <div class="d-image-item">
                            <img src="{{ $application->insideCarImage }}" alt="{{ __('messages.interior_vehicle_image') }}">
                            <div class="d-image-caption">{{ __('messages.interior') }}</div>
                        </div>
                        @endif

                        @if($application->frontSeatsImage)
                        <div class="d-image-item">
                            <img src="{{ $application->frontSeatsImage }}" alt="{{ __('messages.front_seats_image') }}">
                            <div class="d-image-caption">{{ __('messages.front_seats') }}</div>
                        </div>
                        @endif

                        @if($application->backSeatsImage)
                        <div class="d-image-item">
                            <img src="{{ $application->backSeatsImage }}" alt="{{ __('messages.rear_seats_image') }}">
                            <div class="d-image-caption">{{ __('messages.rear_seats') }}</div>
                        </div>
                        @endif


                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="d-card">
                    <div class="d-card-header" id="dDocumentsHeader">
                        <h3 style="color: white;"><i class="fas fa-file-alt"></i> {{ __('messages.required_documents') }}</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="d-card-body" id="dDocumentsBody">
                        <div class="d-images-grid">
                        @if($application->idFrontImage)
                        <div class="d-image-item">
                            <img src="{{ $application->idFrontImage }}" alt="{{ __('messages.id_front_image') }}">
                            <div class="d-image-caption">{{ __('messages.id_front') }}</div>
                        </div>
                        @endif

                        @if($application->idBackImage)
                        <div class="d-image-item">
                            <img src="{{ $application->idBackImage }}" alt="{{ __('messages.id_back_image') }}">
                            <div class="d-image-caption">{{ __('messages.id_back') }}</div>
                        </div>
                        @endif

                        @if($application->licenseFrontImage)
                        <div class="d-image-item">
                            <img src="{{ $application->licenseFrontImage }}" alt="{{ __('messages.license_front_image') }}">
                            <div class="d-image-caption">{{ __('messages.license_front') }}</div>
                        </div>
                        @endif

                        @if($application->licenseBackImage)
                        <div class="d-image-item">
                            <img src="{{ $application->licenseBackImage }}" alt="{{ __('messages.license_back_image') }}">
                            <div class="d-image-caption">{{ __('messages.license_back') }}</div>
                        </div>
                        @endif

                        @if($application->mechanicalImage)
                        <div class="d-image-item">
                            <img src="{{ $application->mechanicalImage }}" alt="{{ __('messages.mechanical_inspection_image') }}">
                            <div class="d-image-caption">{{ __('messages.mechanical_inspection') }}</div>
                        </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-sidebar">
                <!-- Driver Information -->
                <div class="d-card">
                    <div class="d-card-header">
                        <h3 style="color: white;"><i class="fas fa-user"></i> {{ __('messages.driver_information') }}</h3>
                    </div>
                    <div class="d-card-body">
                        <div class="d-info-item">
                            <div class="d-info-label"> {{ __('messages.full_name') }} </div>
                            <div class="d-info-value" > {{ $application->name }} </div>
                        </div>
                        <div class="d-info-item">
                            <div class="d-info-label">{{ __('messages.phone_number') }}</div>
                            <div class="d-info-value">{{ $application->phoneNumber }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-card">
                    <div class="d-card-header">
                        <h3 style="color: white;"><i class="fas fa-cogs"></i> {{ __('messages.actions') }}</h3>
                    </div>
                    <div class="d-card-body">
                        <div class="d-actions">
                            <button class="d-btn d-btn-whatsapp" id="dWhatsappBtn">
                                <i class="fab fa-whatsapp"></i> {{ __('messages.send_whatsapp') }}
                            </button>
                            <button class="d-btn d-btn-success">
                                <i class="fas fa-check"></i> {{ __('messages.accept_application') }}
                            </button>
                            <button class="d-btn d-btn-danger">
                                <i class="fas fa-times"></i> {{ __('messages.reject_application') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Application Status -->
                <div class="d-card">
                    <div class="d-card-header">
                        <h3 style="color: white;"><i class="fas fa-info-circle"></i> {{ __('messages.application_status') }}</h3>
                    </div>
                    <div class="d-card-body">
                        <div class="d-info-item">
                            <div class="d-info-label">{{ __('messages.current_status') }}</div>
                            <div class="d-info-value">{{ __('messages.under_review') }}</div>
                        </div>
                        <div class="d-info-item">
                            <div class="d-info-label">{{ __('messages.submission_date') }}</div>
                            <div class="d-info-value">{{ __('messages.october_15_2023') }}</div>
                        </div>
                        <div class="d-info-item">
                            <div class="d-info-label">{{ __('messages.last_update') }}</div>
                            <div class="d-info-value">{{ __('messages.october_16_2023') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal for image preview -->
<div class="d-modal" id="dImageModal">
    <div class="d-modal-content">
        <span class="d-close-modal" id="dCloseModal">&times;</span>
        <div class="d-modal-image-container" id="dImageContainer">
            <img id="dModalImage" src="" alt="image preview">
        </div>
        <div class="d-modal-caption" id="dModalCaption"></div>
        <div class="d-modal-controls">
            <button class="d-zoom-btn" id="dZoomIn">+</button>
            <button class="d-zoom-btn" id="dZoomOut">-</button>
            <button class="d-zoom-btn" id="dResetZoom">↻</button>
            <span class="d-zoom-level" id="dZoomLevel">100%</span>
        </div>
    </div>
</div>


    <!-- Modal for WhatsApp message -->
    <div class="d-whatsapp-modal" id="dWhatsappModal">
        <div class="d-whatsapp-modal-content">
            <div class="d-whatsapp-modal-header">
                <h3><i class="fab fa-whatsapp"></i> إرسال رسالة واتساب</h3>
                <button class="d-close-whatsapp-modal" id="dCloseWhatsappModal">&times;</button>
            </div>
            <div class="d-message-form">
                <div class="d-form-group">
                    <label for="dMessageText">نص الرسالة:</label>
                    <textarea id="dMessageText" placeholder="اكتب رسالتك هنا..."></textarea>
                </div>
                <div class="d-message-actions">
                    <button class="d-btn d-btn-cancel" id="dCancelMessage">إلغاء</button>
                    <button class="d-btn d-btn-whatsapp" id="dSendWhatsapp">
                        <i class="fab fa-whatsapp"></i> إرسال
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Collapsible sections
        document.querySelectorAll('.d-card-header').forEach(header => {
            // Skip the main sections that shouldn't be collapsible
            if (!header.id.includes('Header')) return;

            header.addEventListener('click', () => {
                const bodyId = header.id.replace('Header', 'Body');
                const body = document.getElementById(bodyId);
                const icon = header.querySelector('i.fa-chevron-down');

                if (body.style.display === 'none') {
                    body.style.display = 'block';
                    header.classList.remove('d-collapsed');
                } else {
                    body.style.display = 'none';
                    header.classList.add('d-collapsed');
                }
            });
        });









// Image modal functionality
const dModal = document.getElementById('dImageModal');
const dModalImg = document.getElementById('dModalImage');
const dImageContainer = document.getElementById('dImageContainer');
const dModalCaption = document.getElementById('dModalCaption');
const dCloseModal = document.getElementById('dCloseModal');
const dZoomIn = document.getElementById('dZoomIn');
const dZoomOut = document.getElementById('dZoomOut');
const dResetZoom = document.getElementById('dResetZoom');
const dZoomLevel = document.getElementById('dZoomLevel');

let currentScale = 1;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;
const minScale = 0.1;
const maxScale = 10;
const scaleStep = 0.25;


function applyScale(scale) {
    currentScale = Math.max(minScale, Math.min(maxScale, scale));
    dModalImg.style.transform = `scale(${currentScale})`;
    dZoomLevel.textContent = `${Math.round(currentScale * 100)}%`;
    updateZoomButtons();
}


function updateZoomButtons() {
    dZoomIn.disabled = currentScale >= maxScale;
    dZoomOut.disabled = currentScale <= minScale;
}


dZoomIn.addEventListener('click', (e) => {
    e.stopPropagation();
    applyScale(currentScale + scaleStep);
});


dZoomOut.addEventListener('click', (e) => {
    e.stopPropagation();
    applyScale(currentScale - scaleStep);
});


dResetZoom.addEventListener('click', (e) => {
    e.stopPropagation();
    applyScale(1);

    dImageContainer.scrollTo({
        left: (dImageContainer.scrollWidth - dImageContainer.clientWidth) / 2,
        top: (dImageContainer.scrollHeight - dImageContainer.clientHeight) / 2,
        behavior: 'smooth'
    });
});


dImageContainer.addEventListener('wheel', (e) => {
    e.preventDefault();

    const rect = dImageContainer.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;

    const scrollX = mouseX + dImageContainer.scrollLeft;
    const scrollY = mouseY + dImageContainer.scrollTop;

    const delta = e.deltaY > 0 ? -scaleStep : scaleStep;
    const newScale = Math.max(minScale, Math.min(maxScale, currentScale + delta));

    if (newScale !== currentScale) {
        const scaleFactor = newScale / currentScale;


        applyScale(newScale);


        dImageContainer.scrollLeft = scrollX * scaleFactor - mouseX;
        dImageContainer.scrollTop = scrollY * scaleFactor - mouseY;
    }
});


dImageContainer.addEventListener('mousedown', (e) => {
    if (currentScale > 1) {
        isDragging = true;
        startX = e.pageX - dImageContainer.offsetLeft;
        startY = e.pageY - dImageContainer.offsetTop;
        scrollLeft = dImageContainer.scrollLeft;
        scrollTop = dImageContainer.scrollTop;
        dImageContainer.style.cursor = 'grabbing';
    }
});

dImageContainer.addEventListener('mouseleave', () => {
    isDragging = false;
    dImageContainer.style.cursor = 'grab';
});

dImageContainer.addEventListener('mouseup', () => {
    isDragging = false;
    dImageContainer.style.cursor = 'grab';
});

dImageContainer.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - dImageContainer.offsetLeft;
    const y = e.pageY - dImageContainer.offsetTop;
    const walkX = (x - startX) * 2;
    const walkY = (y - startY) * 2;
    dImageContainer.scrollLeft = scrollLeft - walkX;
    dImageContainer.scrollTop = scrollTop - walkY;
});


let touchStartX, touchStartY, touchScrollLeft, touchScrollTop;

dImageContainer.addEventListener('touchstart', (e) => {
    if (currentScale > 1) {
        const touch = e.touches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
        touchScrollLeft = dImageContainer.scrollLeft;
        touchScrollTop = dImageContainer.scrollTop;
    }
});

dImageContainer.addEventListener('touchmove', (e) => {
    if (currentScale > 1) {
        e.preventDefault();
        const touch = e.touches[0];
        const walkX = (touch.clientX - touchStartX) * 2;
        const walkY = (touch.clientY - touchStartY) * 2;
        dImageContainer.scrollLeft = touchScrollLeft - walkX;
        dImageContainer.scrollTop = touchScrollTop - walkY;
    }
});


document.querySelectorAll('.d-image-item').forEach(item => {
    item.addEventListener('click', () => {
        const imgElement = item.querySelector('img');
        const captionElement = item.querySelector('.d-image-caption');

        if (imgElement && imgElement.src) {
            dModal.style.display = 'flex';
            dModalImg.src = imgElement.src;
            dModalImg.alt = imgElement.alt;


            setTimeout(() => {
                applyScale(1);
                dImageContainer.scrollTo({
                    left: (dImageContainer.scrollWidth - dImageContainer.clientWidth) / 2,
                    top: (dImageContainer.scrollHeight - dImageContainer.clientHeight) / 2
                });
            }, 50);


            if (captionElement) {
                dModalCaption.textContent = captionElement.textContent;
            } else {
                dModalCaption.textContent = '';
            }

            document.body.style.overflow = 'hidden';
        }
    });
});


function closeModal() {
    dModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

dCloseModal.addEventListener('click', closeModal);

dModal.addEventListener('click', (e) => {
    if (e.target === dModal) {
        closeModal();
    }
});

// إغلاق المودال بالزر ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dModal.style.display === 'flex') {
        closeModal();
    }
});





        // // Image modal functionality
        // const dModal = document.getElementById('dImageModal');
        // const dModalImg = document.getElementById('dModalImage');
        // const dCloseModal = document.getElementById('dCloseModal');

        // document.querySelectorAll('.d-image-item').forEach(item => {
        //     item.addEventListener('click', () => {
        //         dModal.style.display = 'flex';
        //         dModalImg.src = item.getAttribute('data-src');
        //     });
        // });

        // dCloseModal.addEventListener('click', () => {
        //     dModal.style.display = 'none';
        // });

        // dModal.addEventListener('click', (e) => {
        //     if (e.target === dModal) {
        //         dModal.style.display = 'none';
        //     }
        // });


        // WhatsApp modal functionality
        const dWhatsappModal = document.getElementById('dWhatsappModal');
        const dWhatsappBtn = document.getElementById('dWhatsappBtn');
        const dCloseWhatsappModal = document.getElementById('dCloseWhatsappModal');
        const dCancelMessage = document.getElementById('dCancelMessage');
        const dSendWhatsapp = document.getElementById('dSendWhatsapp');
        const dMessageText = document.getElementById('dMessageText');

        dWhatsappBtn.addEventListener('click', () => {
            dWhatsappModal.style.display = 'flex';
            dMessageText.focus();
        });

        dCloseWhatsappModal.addEventListener('click', () => {
            dWhatsappModal.style.display = 'none';
        });

        dCancelMessage.addEventListener('click', () => {
            dWhatsappModal.style.display = 'none';
            dMessageText.value = '';
        });

        dSendWhatsapp.addEventListener('click', () => {
            const phoneNumber = document.getElementById('dDriverPhoneValue').textContent.replace(/\D/g, '');
            const message = encodeURIComponent(dMessageText.value);

            if (dMessageText.value.trim() === '') {
                alert('يرجى كتابة رسالة قبل الإرسال');
                return;
            }

            // Open WhatsApp with the message
            window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
            dWhatsappModal.style.display = 'none';
            dMessageText.value = '';
        });

        // Sample data population (in a real app, this would come from your backend)
        document.addEventListener('DOMContentLoaded', () => {
            // This is where you would fetch the actual data from your backend
            // and populate the fields accordingly

            // Example of populating data (replace with actual data)
            const driverData = {
                name: 'Mousab Al-syoufi',
                phoneNumber: '0937766225',
                brand: 'تويوتا',
                model: 'كامري',
                year: '2022',
                color: 'أبيض',
                plateNumber: 'أ ب ج 1234',
                profileImage: 'https://via.placeholder.com/100',
                idFrontImage: 'https://via.placeholder.com/300x200',
                idBackImage: 'https://via.placeholder.com/300x200',
                licenseFrontImage: 'https://via.placeholder.com/300x200',
                licenseBackImage: 'https://via.placeholder.com/300x200',
                mechanicalImage: 'https://via.placeholder.com/300x200',
                frontCarImage: 'https://via.placeholder.com/300x200',
                backCarImage: 'https://via.placeholder.com/300x200',
                rightCarImage: 'https://via.placeholder.com/300x200',
                leftCarImage: 'https://via.placeholder.com/300x200',
                insideCarImage: 'https://via.placeholder.com/300x200',
                frontSeatsImage: 'https://via.placeholder.com/300x200',
                backSeatsImage: 'https://via.placeholder.com/300x200'
            };

            // Populate the fields with data
            document.getElementById('dDriverName').textContent = driverData.name;
            document.getElementById('dDriverPhone').textContent = driverData.phoneNumber;
            document.getElementById('dDriverNameValue').textContent = driverData.name;
            document.getElementById('dDriverPhoneValue').textContent = driverData.phoneNumber;
            document.getElementById('dProfileImage').src = driverData.profileImage;
            document.getElementById('dCarBrand').textContent = driverData.brand;
            document.getElementById('dCarModelValue').textContent = driverData.model;
            document.getElementById('dCarYear').textContent = driverData.year;
            document.getElementById('dCarColor').textContent = driverData.color;
            document.getElementById('dPlateNumber').textContent = driverData.plateNumber;
            document.getElementById('dCarModel').textContent = `${driverData.brand} ${driverData.model} ${driverData.year}`;

            // Set image sources (in a real app, you would loop through and set each one)
            // This is just a simplified example
        });
    </script>
</body>

</x-master-layout>
