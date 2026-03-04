import 'package:flutter/widgets.dart';
import 'package:flutter_svg/flutter_svg.dart';

class AppIcons {
  static const _base = 'assets/icons/';

  static const wallet = '${_base}wallet.svg';
  static const scanQr = '${_base}scan_qr.svg';
  static const sendMoney = '${_base}send_money.svg';
  static const request = '${_base}request.svg';
  static const eSoko = '${_base}e_soko.svg';
  static const justEat = '${_base}just_eat.svg';
  static const twende = '${_base}twende.svg';
  static const shuleYetu = '${_base}shule_yetu.svg';
  static const myChamaa = '${_base}my_chamaa.svg';
  static const hospital = '${_base}hospital.svg';
  static const myHasol = '${_base}my_hasol.svg';
  static const eventsTix = '${_base}events_tix.svg';
  static const navHome = '${_base}nav_home.svg';
  static const navServices = '${_base}nav_services.svg';
  static const navWallet = '${_base}nav_wallet.svg';
  static const navProfile = '${_base}nav_profile.svg';
  static const navChat = '${_base}nav_chat.svg';
  static const kenyaFlag = '${_base}kenya_flag.svg';
  static const search = '${_base}search.svg';
  static const mic = '${_base}mic.svg';
  static const eGrocery = '${_base}e_grocery.svg';
  static const parcelDelivery = '${_base}parcel_delivery.svg';
  static const kenyaAcademy = '${_base}kenya_academy.svg';
  static const propertyRent = '${_base}property_rent.svg';
  static const savings = '${_base}savings.svg';
  static const getLoan = '${_base}get_loan.svg';
  static const wajibu = '${_base}wajibu.svg';
  static const govtServices = '${_base}govt_services.svg';
  static const mail = '${_base}mail.svg';
  static const bell = '${_base}bell.svg';

  static Widget svg(
    String asset, {
    double? width,
    double? height,
    ColorFilter? colorFilter,
  }) {
    return SvgPicture.asset(
      asset,
      width: width,
      height: height,
      colorFilter: colorFilter,
    );
  }
}
