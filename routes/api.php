<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\PaymentHistoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Merchant\BookingController;
use App\Http\Controllers\Merchant\MerchantSettingController;
use App\Http\Controllers\Merchant\MinisiteController;
use App\Http\Controllers\Merchant\ServicesController;
use App\Http\Controllers\Merchant\StaffController;
use App\Http\Controllers\Merchant\SubscriptionController;
use App\Http\Controllers\Merchant\GlobalsettingController;
use App\Http\Controllers\Merchant\TransactionController;
use App\Http\Controllers\Merchant\MerchantDashboardContoller;
use App\Http\Controllers\Merchant\AnalyticesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;



// user login
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/marchant/register', [AuthController::class, 'marchantregister'])->name('marchant.register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPasswordWithOtp']);



// google login api
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Admin Protected Routes
Route::middleware(['auth:api'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/index', [AuthController::class, 'index'])->name('index');
    Route::post('/register', [AuthController::class, 'adminregister'])->name('register');
    Route::get('/edit/{id}', [AuthController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [AuthController::class, 'adminUpdate'])->name('update');
    // Route::delete('/delete/{id}', [AuthController::class, 'delete'])->name('delete');
    Route::post('/logout/{id}', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password/{id}', [AuthController::class, 'password'])->name('password');
    Route::post('/passwordchange/{id}', [AuthController::class, 'passwordchange'])->name('passwordchange');

    // ----- Show and Update Personal Information
    Route::get('profile-info', [AuthController::class, 'profileInfo'])->name('profileInfo');
    Route::post('saveinfo', [AuthController::class, 'saveInfo'])->name('saveInfo');

    // dashboard
   Route::get('dashboard-overview', [DashboardController::class, 'index'])->name('dashboard-overview');
   Route::get('monthlypaymentCount', [DashboardController::class, 'monthlypaymentCount'])->name('monthlypaymentCount');
   Route::get('weeklyPaymentCount', [DashboardController::class, 'weeklyPaymentCount'])->name('weeklyPaymentCount');


    // Role
    Route::prefix('role')->group(function () {
        Route::get('index', [RoleController::class, 'index'])->name('role.index');
        Route::post('store', [RoleController::class, 'store'])->name('role.store');
        Route::get('edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
        Route::post('update/{id}', [RoleController::class, 'update'])->name('role.update');
    });
    // permission
    Route::prefix('permission')->group(function () {
        Route::get('index', [PermissionController::class, 'index'])->name('permission.index');
        Route::post('store', [PermissionController::class, 'store'])->name('permission.store');
        Route::get('edit/{id}', [PermissionController::class, 'edit'])->name('permission.edit');
        Route::post('update/{id}', [PermissionController::class, 'update'])->name('permission.update');
        Route::delete('delete/{id}', [PermissionController::class, 'destroy'])->name('permission.destroy');
    });
    // category
    Route::prefix('category')->group(function () {
        // Route::get('index', [CategoryController::class, 'index'])->name('category.index');
        Route::post('store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('update/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
    });
    // subcategory
    Route::prefix('subcategory')->group(function () {
        Route::get('index', [SubcategoryController::class, 'index'])->name('subcategory.index');
        Route::post('store', [SubcategoryController::class, 'store'])->name('subcategory.store');
        Route::get('edit/{id}', [SubcategoryController::class, 'edit'])->name('subcategory.edit');
        Route::post('update/{id}', [SubcategoryController::class, 'update'])->name('subcategory.update');
        Route::delete('/delete/{id}', [SubcategoryController::class, 'destroy'])->name('subcategory.destroy');
    });

    // Brand
    Route::prefix('brand')->group(function () {
        Route::get('index', [BrandController::class, 'index'])->name('brand.index');
        Route::post('store', [BrandController::class, 'store'])->name('brand.store');
        Route::get('edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::post('update/{id}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/delete/{id}', [BrandController::class, 'destroy'])->name('brand.destroy');
    });

    // Slider
    Route::prefix('slider')->group(function () {
        Route::get('index', [SliderController::class, 'index'])->name('slider.index');
        Route::post('store', [SliderController::class, 'store'])->name('slider.store');
        Route::get('edit/{id}', [SliderController::class, 'edit'])->name('slider.edit');
        Route::post('update/{id}', [SliderController::class, 'update'])->name('slider.update');
        Route::delete('delete/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
    });

    // faq-category
    Route::prefix('faq-categories')->group(function () {
        Route::get('index', [FaqCategoryController::class, 'index'])->name('faq-categories.index');
        Route::post('store', [FaqCategoryController::class, 'store'])->name('faq-categories.store');
        Route::get('edit/{id}', [FaqCategoryController::class, 'edit'])->name('faq-categories.edit');
        Route::post('update/{id}', [FaqCategoryController::class, 'update'])->name('faq-categories.update');
        Route::delete('delete/{id}', [FaqCategoryController::class, 'destroy'])->name('faq-categories.destroy');
    });

    // faq
    Route::prefix('faq')->group(function () {
        Route::get('index', [FaqController::class, 'index'])->name('faq.index');
        Route::post('store', [FaqController::class, 'store'])->name('faq.store');
        Route::get('edit/{id}', [FaqController::class, 'edit'])->name('faq.edit');
        Route::post('update/{id}', [FaqController::class, 'update'])->name('faq.update');
        Route::delete('delete/{id}', [FaqController::class, 'destroy'])->name('faq.destroy');
    });
    // mini-site
    Route::prefix('mini-sites')->group(function () {
        Route::post('store', [MinisiteController::class, 'store'])->name('mini-sites.store');
        Route::get('show', [MinisiteController::class, 'show'])->name('mini-sites.show');
        Route::post('update', [MinisiteController::class, 'update'])->name('mini-sites.update');
    });
    // merchant-setting
    Route::prefix('merchant-setting')->group(function () {
        Route::post('store', [MerchantSettingController::class, 'store'])->name('merchant.store');
        Route::get('show', [MerchantSettingController::class, 'show'])->name('merchant.show');

    });
    // merchant subscription
    Route::prefix('process')->group(function () {
        Route::get('index', [SubscriptionController::class, 'index'])->name('process.index');
        Route::post('store', [SubscriptionController::class, 'store'])->name('process.store');
        Route::get('edit/{id}', [SubscriptionController::class, 'edit'])->name('process.edit');
        Route::post('update/{id}', [SubscriptionController::class, 'update'])->name('process.update');
        Route::delete('delete/{id}', [SubscriptionController::class, 'destroy'])->name('process.destroy');
    });
    // admin subscription
    Route::prefix('subscription')->group(function () {
        Route::get('index', [AdminSubscriptionController::class, 'index'])->name('process.index');
        Route::get('edit/{id}', [AdminSubscriptionController::class, 'show'])->name('process.edit');
        Route::post('update/{id}', [AdminSubscriptionController::class, 'update'])->name('process.update');
    });

    // setting
    Route::prefix('setting')->group(function () {
        Route::get('index', [SettingController::class, 'index'])->name('setting.index');
        Route::post('update', [SettingController::class, 'update'])->name('setting.update');
    });
    // notification
    Route::prefix('notification')->group(function () {
        Route::post('/send-notification', [NotificationController::class, 'sendNotification'])->name('notification.store');
    });

    // mail
    Route::prefix('mail')->group(function () {
        Route::post('/send-email', [EmailController::class, 'sendEmail']);
    });

    // ----- Merchant/Service
    Route::prefix('service')->group(function () {
        Route::get('index', [ServicesController::class, 'index'])->name('service.index');
        Route::post('store', [ServicesController::class, 'store'])->name('service.store');
        Route::get('show/{id}', [ServicesController::class, 'show'])->name('service.show');
        Route::put('update/{id}', [ServicesController::class, 'update'])->name('service.update');
        Route::delete('delete/{id}', [ServicesController::class, 'destroy'])->name('service.destroy');
    });

    // ----- Merchant/Staff
    Route::prefix('staff')->group(function () {
        Route::get('index', [StaffController::class, 'index'])->name('staff.index');
        Route::post('store', [StaffController::class, 'store'])->name('staff.store');
        Route::get('show/{id}', [StaffController::class, 'show'])->name('staff.show');
        Route::put('update/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('delete/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });

    // ----- Admin/Subscription/Plan
    Route::prefix('plan')->group(function () {
        Route::get('index', [PlanController::class, 'index'])->name('plan.index');
        Route::post('store', [PlanController::class, 'store'])->name('plan.store');
        Route::get('show/{id}', [PlanController::class, 'show'])->name('plan.show');
        Route::put('update/{id}', [PlanController::class, 'update'])->name('plan.update');
        Route::delete('delete/{id}', [PlanController::class, 'destroy'])->name('plan.destroy');
        Route::patch('update-status/{id}', [PlanController::class, 'updateStatus'])->name('plan.updateStatus');
    });

    // ----- Admin/Payments
    Route::prefix('payment-history')->group(function () {
        Route::get('index', [PaymentHistoryController::class, 'index'])->name('payment-history.index');
        Route::get('show/{id}', [PaymentHistoryController::class, 'show'])->name('payment-history.show');
        Route::post('update/{id}', [PaymentHistoryController::class, 'update'])->name('payment-history.update');
        Route::post('{id}/sendEmail', [PaymentHistoryController::class, 'sendEmail'])->name('payment-history.sendEmail');
        Route::patch('updateStatus/{id}', [PaymentHistoryController::class, 'updateStatus'])->name('payment-history.updateStatus');
    });

    // ----- Merchant/Bookings
    Route::prefix('booking')->group(function () {
        Route::get('index', [BookingController::class, 'index'])->name('booking.index');
        Route::post('store', [BookingController::class, 'store'])->name('booking.store');
        Route::get('show/{id}', [BookingController::class, 'show'])->name('booking.show');
        Route::post('update/{id}', [BookingController::class, 'update'])->name('booking.update');
        Route::get('schedule', [BookingController::class, 'getAvailability'])->name('booking.getAvailability');
        Route::get('staff', [BookingController::class, 'getAvailableStaffByTime'])->name('booking.getAvailableStaffByTime');
        Route::post('service-booking', [BookingController::class, 'bookingByUser'])->name('booking.bookingByUser');
    });

    // ----- Admin/Merchants
    Route::prefix('merchant')->group(function () {
        Route::get('index', [MerchantController::class, 'index'])->name('merchant.index');
        Route::get('show/{id}', [MerchantController::class, 'show'])->name('merchant.show');
        Route::put('update/{id}', [MerchantController::class, 'update'])->name('merchant.update');
    });

    //----- User/Dashboard/Booking History.....
    Route::prefix('dashboard')->group(function () {
        Route::get('upcoming', [UserDashboardController::class, 'Upcoming'])->name('dashboard.upcoming');
        Route::get('history', [UserDashboardController::class, 'History'])->name('dashboard.history');
        Route::get('activity', [UserDashboardController::class, 'Activity'])->name('dashboard.activity');
        Route::get('show/{id}', [UserDashboardController::class, 'show'])->name('dashboard.show');
        Route::get('payment-history', [UserDashboardController::class, 'paymentHistory'])->name('dashboard.paymentHistory');
        Route::get('show-payment/{id}', [UserDashboardController::class, 'showPayment'])->name('dashboard.showPayment');
        Route::get('view-order-details/{id}', [UserDashboardController::class, 'viewOrderDetails'])->name('dashboard.viewOrderDetails');
        Route::get('cancel-preview/{id}', [UserDashboardController::class, 'cancelPreview'])->name('dashboard.cancelPreview');
        Route::patch('cancel-booking/{id}', [UserDashboardController::class, 'cancelBooking'])->name('dashboard.cancelBooking');
        Route::get('reschedule-preview/{id}', [UserDashboardController::class, 'reschedulePreview'])->name('dashboard.reschedulePreview');
        Route::post('reschedule-booking/{id}', [UserDashboardController::class, 'rescheduleBooking'])->name('dashboard.rescheduleBooking');
    });

    // TransactionController
    Route::prefix('transaction')->group(function () {
        Route::get('index', [TransactionController::class, 'index'])->name('Transaction.index');
        Route::get('show/{id}', [TransactionController::class, 'show'])->name('Transaction.show');

    });
    // merchantdashboard
    Route::prefix('mer-dashboard')->group(function () {
        Route::get('index', [MerchantDashboardContoller::class, 'index'])->name('merchantdashboard.index');
        Route::get('revenue', [MerchantDashboardContoller::class, 'monthlypaymentrevenue'])->name('merchantdashboard.monthlypaymentrevenue');
        Route::get('weeklyrevenue', [MerchantDashboardContoller::class, 'weeklyPaymentrevenue'])->name('merchantdashboard.weeklyPaymentrevenue');
        Route::get('today', [MerchantDashboardContoller::class, 'todayAppointment'])->name('merchantdashboard.todayAppointment');
    });
    // merchantdashboard
    Route::prefix('analytics')->group(function () {

        Route::get('index', [AnalyticesController::class, 'analytice'])->name('merchantdashboard.analytice');
        Route::get('monthlyrevenue', [AnalyticesController::class, 'monthlypaymentrevenue'])->name('merchantdashboard.monthlyPaymentRevenue');
        Route::get('weeklyrevenue', [AnalyticesController::class, 'weeklyPaymentrevenue'])->name('merchantdashboard.weeklyPaymentRevenue');
        Route::get('newreturn', [AnalyticesController::class, 'newreturn'])->name('merchantdashboard.newreturn');
        Route::get('staffPerformance', [AnalyticesController::class, 'staffPerformance'])->name('merchantdashboard.staffPerformance');
    });

 });


Route::get('/admin/process/callback', [SubscriptionController::class, 'tapCallback'])->name('admin.process.callback');
// Route::get('/admin/process/callback', [SubscriptionController::class, 'tapCallback'])->name('admin.process.callback');
