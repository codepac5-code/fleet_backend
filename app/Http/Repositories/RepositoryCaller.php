<?php
namespace App\Http\Repositories;


use App\Http\Repositories\CityRepositories\CityRepositoryCaller;
use App\Http\Repositories\UserRepositories\UserRepositoryCaller;
use App\Http\Repositories\AdminRepositories\AdminRepositoryCaller;
use App\Http\Repositories\CoponRepositories\CouponRepositoryCaller;
use App\Http\Repositories\StateRepositories\StateRepositoryCaller;
use App\Http\Repositories\DriverRepositories\DriverRepositoryCaller;
use App\Http\Repositories\OfficeRepositories\OfficeRepositoryCaller;
use App\Http\Repositories\RatingRepositories\RatingRepositoryCaller;
use App\Http\Repositories\SliderRepositories\SliderRepositoryCaller;
use App\Http\Repositories\WalletRepositories\WalletRepositoryCaller;
use App\Http\Repositories\AddressRepositories\AddressRepositoryCaller;
use App\Http\Repositories\BookingRepositories\BookingRepositoryCaller;
use App\Http\Repositories\CountryRepositories\CountryRepositoryCaller;
use App\Http\Repositories\ServiceRepositories\ServiceRepositoryCaller;
use App\Http\Repositories\VehicleRepositories\VehicleRepositoryCaller;
use App\Http\Repositories\DocumentRepositories\DocumentRepositoryCaller;
use App\Http\Repositories\SettingsRepositories\SettingsRepositoryCaller;
use App\Http\Repositories\CoponUserRepositories\CoponUserRepositoryCaller;
use App\Http\Repositories\MtnInvoiceRepositories\MtnInvoiceRepositoryCaller;
use App\Http\Repositories\RatingUserRepositories\RatingUserRepositoryCaller;
use App\Http\Repositories\SubServiceRepositories\SubServiceRepositoryCaller;
use App\Http\Repositories\UserWalletRepositories\UserWalletRepositoryCaller;
use App\Http\Repositories\NotficationRepositories\NotficationRepositoryCaller;
use App\Http\Repositories\CoponServiceRepositories\CouponServiceRepositoryCaller;
use App\Http\Repositories\OfficeWalletRepositories\OfficeWalletRepositoryCaller;
use App\Http\Repositories\DrieverWalletRepositories\DrieverWalletRepositoryCaller;
use App\Http\Repositories\PaymentMethodRepositories\PaymentMethodRepositoryCaller;
use App\Http\Repositories\OfficeDocumentRepositories\OfficeDocumentRepositoryCaller;
use App\Http\Repositories\FrontendSettingRepositories\FrontendSettingRepositoryCaller;
use App\Http\Repositories\SubscriptionPolicyRepositories\SubscriptionPolicyRepositoryCaller;
use App\Http\Repositories\DriverAddressRepositories\DriverAddressRepositoryCaller;
use App\Http\Repositories\OfficeAddressRepositories\OfficeAddressRepositoryCaller;
use App\Http\Repositories\DriverPayoutRepositories\DriverPayoutRepositoryCaller;
use App\Http\Repositories\VehicleBrandRepositories\VehicleBrandRepositoryCaller;
use App\Http\Repositories\regionRepositories\regionRepositoryCaller; 
use App\Http\Repositories\FleetStatisticRepositories\FleetStatisticRepositoryCaller; 
use App\Http\Repositories\FleetRepositories\FleetRepositoryCaller; 
use App\Http\Repositories\FleetOfficeRepositories\FleetOfficeRepositoryCaller; 
use App\Http\Repositories\CommissionsRepositories\CommissionsRepositoryCaller; 
use App\Http\Repositories\CommissionEarningsRepositories\CommissionEarningsRepositoryCaller; 
use App\Http\Repositories\WalletTransactionRepositories\WalletTransactionRepositoryCaller; 
use App\Http\Repositories\Vehicle_SubServiceRepositories\Vehicle_SubServiceRepositoryCaller; 
use App\Http\Repositories\UserReportRepositories\UserReportRepositoryCaller; 
use App\Http\Repositories\PublicUserAppSettingRepositories\PublicUserAppSettingRepositoryCaller; 
use App\Http\Repositories\PublicDriverAppSettingRepositories\PublicDriverAppSettingRepositoryCaller; 
use App\Http\Repositories\SyriatelInvoiceRepositories\SyriatelInvoiceRepositoryCaller; 
class RepositoryCaller {

	static public function SyriatelInvoiceRepository(){return (new SyriatelInvoiceRepositoryCaller);}
	static public function PublicDriverAppSettingRepository(){return (new PublicDriverAppSettingRepositoryCaller);}
	static public function PublicUserAppSettingRepository(){return (new PublicUserAppSettingRepositoryCaller);}
	static public function UserReportRepository(){return (new UserReportRepositoryCaller);}
	static public function Vehicle_SubServiceRepository(){return (new Vehicle_SubServiceRepositoryCaller);}
	static public function WalletTransactionRepository(){return (new WalletTransactionRepositoryCaller);}
	static public function CommissionEarningsRepository(){return (new CommissionEarningsRepositoryCaller);}
	static public function CommissionsRepository(){return (new CommissionsRepositoryCaller);}
	static public function FleetOfficeRepository(){return (new FleetOfficeRepositoryCaller);}
	static public function FleetRepository(){return (new FleetRepositoryCaller);}
	static public function FleetStatisticRepository(){return (new FleetStatisticRepositoryCaller);}
	static public function regionRepository(){return (new regionRepositoryCaller);}
	static public function OfficeAddressRepository(){return (new OfficeAddressRepositoryCaller);}
	static public function DriverAddressRepository(){return (new DriverAddressRepositoryCaller);}
	static public function VehicleBrandRepository(){return (new VehicleBrandRepositoryCaller);}
	static public function DriverPayoutRepository(){return (new DriverPayoutRepositoryCaller);}
	static public function FrontendSettingRepository(){return (new FrontendSettingRepositoryCaller);}
	static public function VehicleRepository(){return (new VehicleRepositoryCaller);}
	static public function NotficationRepository(){return (new NotficationRepositoryCaller);}
	static public function SettingsRepository(){return (new SettingsRepositoryCaller);}
	static public function RatingUserRepository(){return (new RatingUserRepositoryCaller);}
	static public function RatingRepository(){return (new RatingRepositoryCaller);}
	static public function DrieverWalletRepository(){return (new DrieverWalletRepositoryCaller);}
	static public function UserWalletRepository(){return (new UserWalletRepositoryCaller);}
	static public function MtnInvoiceRepository(){return (new MtnInvoiceRepositoryCaller);}
	static public function BookingRepository(){return (new BookingRepositoryCaller);}
	static public function DocumentRepository(){return (new DocumentRepositoryCaller);}
	static public function OfficeDocumentRepository(){return (new OfficeDocumentRepositoryCaller);}
	static public function OfficeWalletRepository(){return (new OfficeWalletRepositoryCaller);}
	static public function WalletRepository(){return (new WalletRepositoryCaller);}
	static public function AdminRepository(){return (new AdminRepositoryCaller);}
	static public function StateRepository(){return (new StateRepositoryCaller);}
	static public function CountryRepository(){return (new CountryRepositoryCaller);}
	static public function CityRepository(){return (new CityRepositoryCaller);}
	static public function DriverRepository(){return (new DriverRepositoryCaller);}
	static public function CouponServiceRepository(){return (new CouponServiceRepositoryCaller);}
	static public function CoponUserRepository(){return (new CoponUserRepositoryCaller);}
	static public function CouponRepository(){return (new CouponRepositoryCaller);}
	static public function SubServiceRepository(){return (new SubServiceRepositoryCaller);}
	static public function PaymentMethodRepository(){return (new PaymentMethodRepositoryCaller);}
	static public function ServiceRepository(){return (new ServiceRepositoryCaller);}
	static public function SliderRepository(){return (new SliderRepositoryCaller);}
	static public function SubscriptionPolicyRepository(){return (new SubscriptionPolicyRepositoryCaller);}
	static public function UserRepository(){return (new UserRepositoryCaller);}
	static public function OfficeRepository(){return (new OfficeRepositoryCaller);}
	static public function AddressRepository(){return (new AddressRepositoryCaller);}

}
