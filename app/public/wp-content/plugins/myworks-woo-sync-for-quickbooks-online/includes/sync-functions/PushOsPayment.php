<?php
if ( ! defined( 'ABSPATH' ) )
exit;

/**
 * Push Order Payment Into Quickbooks Online.
 *
 * @since    
 * Last Updated: 2019-01-25
*/

$include_this_function = true;

if($include_this_function){
	if($this->is_connected()){
		//$this->_p($payment_data);
		
		if($this->option_checked('mw_wc_qbo_sync_order_as_sales_receipt')){
			return false;
		}
		
		$manual = $this->get_array_isset($payment_data,'manual',false);
		$payment_id = (int) $this->get_array_isset($payment_data,'wc_inv_id',0);

		$wc_inv_num = $this->get_array_isset($payment_data,'wc_inv_num','');

		$ord_id_num = ($wc_inv_num!='')?$wc_inv_num:$payment_id;

		$qbo_customer_id = (int) $this->get_array_isset($payment_data,'qbo_customerid',0);
		
		$wc_inv_id = (int) $this->get_array_isset($payment_data,'order_id',0);
		$wc_cus_id = (int) $this->get_array_isset($payment_data,'customer_user',0);
		
		//
		$payment_amount = $this->get_array_isset($payment_data,'_order_total',0);
		$_paid_date = $this->get_array_isset($payment_data,'wc_inv_date','',true);
		$wc_inv_date = $this->get_array_isset($payment_data,'wc_inv_date','',true);
		$_order_currency = $this->get_array_isset($payment_data,'_order_currency','',true);
		
		if(isset($payment_data['ds_ori_idata']) && is_array($payment_data['ds_ori_idata']) && count($payment_data['ds_ori_idata'])){
			$ds_ori_idata = $payment_data['ds_ori_idata'];
			$ds_payment_id = (int) $this->get_array_isset($ds_ori_idata,'wc_inv_id',0);
			$ds_wc_inv_num = $this->get_array_isset($ds_ori_idata,'wc_inv_num','');
			$qbo_invoice_id = (int) $this->get_qbo_invoice_id($ds_payment_id,$ds_wc_inv_num);
			
			if($this->get_array_isset($payment_data,'_payment_method','',true)==''){
				$payment_data = $ds_ori_idata;
			}				
		}else{				
			$qbo_invoice_id = (int) $this->get_qbo_invoice_id($payment_id,$wc_inv_num);
		}
		
		if(!$qbo_invoice_id){
			$this->save_log('Export Payment Error Order #'.$ord_id_num,'QuickBooks invoice not found!','Payment',0);
			return false;
		}
		
		/**/
		if(!$qbo_customer_id && isset($payment_data['mw_qbo_yithwgcp']) && isset($payment_data['mw_qbo_yithwgcp'])){
			/*Custom Post Customer Support*/
			if($this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
				$wc_custom_cus_id = (isset($payment_data['assigned_customer_id']))?(int) $payment_data['assigned_customer_id']:0;
				if(!$wc_custom_cus_id){
					$wc_custom_cus_id = (int) get_post_meta( $wc_inv_id, 'assigned_customer_id', true );
				}
				
				if($wc_custom_cus_id > 0){
					$qbo_customerid = (int) $this->get_field_by_val($wpdb->prefix.'mw_wc_qbo_sync_customer_pairs','qbo_customerid','wc_customerid',$wc_custom_cus_id);
				}
			}
			
			if(!$this->is_plugin_active('customer-custom-post-type-map-for-myworks-qbo-sync')){
				if($this->option_checked('mw_wc_qbo_sync_orders_to_specific_cust_opt')){
					if($wc_cus_id){
						$user_info = get_userdata($wc_cus_id);
						$io_cs = false;
						if(isset($user_info->roles) && is_array($user_info->roles)){
							$sc_roles_as_cus = $this->get_option('mw_wc_qbo_sync_wc_cust_role_sync_as_cus');
							if(!empty($sc_roles_as_cus)){
								$sc_roles_as_cus = explode(',',$sc_roles_as_cus);
								if(is_array($sc_roles_as_cus) && count($sc_roles_as_cus)){
									foreach($sc_roles_as_cus as $sr){
										if(in_array($sr,$user_info->roles)){
											$io_cs = true;
											break;
										}
									}
								}
							}
						}
						
						if($io_cs){
							$qbo_customer_id = (int) $this->check_save_get_qbo_customer_id($customer_data);
						}else{
							$qbo_customer_id = (int) $this->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
						}
						
					}else{
						$qbo_customer_id = (int) $this->get_option('mw_wc_qbo_sync_orders_to_specific_cust');
					}			
				}else{					
					if($wc_cus_id){
						$qbo_customer_id = (int) $this->check_save_get_qbo_customer_id($customer_data);
					}else{
						$qbo_customer_id = (int) $this->check_save_get_qbo_guest_id($customer_data);
					}
				}
			}
		}
		
		//Customer currency validate for multicurrency
		if($qbo_customer_id && $this->get_qbo_company_setting('is_m_currency')){
			#$_order_currency = $this->get_array_isset($payment_data,'_order_currency','',true);
			$qb_currency_cust_id = (int) $this->validate_get_currency_qb_cust_id($qbo_customer_id,$_order_currency);
			if($qb_currency_cust_id > 0){
				$qbo_customer_id = $qb_currency_cust_id;
			}
		}
		
		if(!$qbo_customer_id){
			$this->save_log('Export Payment Error #'.$payment_id,'QuickBooks customer not found!','Payment',0);
			return false;
		}
		
		if($this->if_sync_os_payment($payment_data)){			
			if(!$this->check_os_payment_get_obj($payment_data,$qbo_invoice_id,$qbo_customer_id)){
				
				$Context = $this->Context;
				$realm = $this->realm;

				$PaymentService = new QuickBooks_IPP_Service_Payment();
				$Payment = new QuickBooks_IPP_Object_Payment();

				$_payment_method = $this->get_array_isset($payment_data,'_payment_method','',true);
				$_payment_method_title = $this->get_array_isset($payment_data,'_payment_method_title','',true);

				//$_order_currency = $this->get_array_isset($payment_data,'_order_currency','',true);
				$pm_map_data = $this->get_mapped_payment_method_data($_payment_method,$_order_currency);

				$enable_payment = (int) $this->get_array_isset($pm_map_data,'enable_payment',0);
				//$_paid_date = $this->get_array_isset($payment_data,'wc_inv_date','',true);
				//$wc_inv_date = $this->get_array_isset($payment_data,'wc_inv_date','',true);

				//$payment_amount = $this->get_array_isset($payment_data,'_order_total',0);
				$payment_amount = floatval($payment_amount);
				
				if(isset($payment_data['mw_qbo_yithwgcp']) && isset($payment_data['mw_qbo_yithwgcp'])){
					$enable_payment = 1;
					$payment_amount = floatval($payment_data['_ywgc_applied_gift_cards_totals']);
				}
				
				if($enable_payment && $payment_amount>0){
					if($_paid_date==''){
						$this->save_log('Export Payment Error Order #'.$ord_id_num,'Payment date not found!','Payment',0);
						return false;
					}

					$_transaction_id = $this->get_array_isset($payment_data,'transaction_id','',true);

					$_paid_date = $this->view_date($_paid_date);

					$qb_p_method_id = (int) $this->get_array_isset($pm_map_data,'qb_p_method_id',0);
					$qbo_account_id = (int) $this->get_array_isset($pm_map_data,'qbo_account_id',0);

					$enable_batch = (int) $this->get_array_isset($pm_map_data,'enable_batch',0);

					//$Payment->setPaymentRefNum($_transaction_id);
					//$Payment->setPaymentRefNum('Order-'.$payment_id);
					
					/**/
					$RefNum = $ord_id_num;
					if($this->get_option('mw_wc_qbo_sync_qb_pmnt_ref_num_vf') == 'TXN_ID' && !empty($_transaction_id)){
						$RefNum = $_transaction_id;
					}
					
					if(strlen($RefNum) > 21){
						$RefNum = substr($RefNum,-21);
					}
					
					$Payment->setPaymentRefNum($RefNum);
					
					/**/
					if(isset($payment_data['mw_qbo_yithwgcp']) && isset($payment_data['yith_gift_card_no']) && !empty($payment_data['yith_gift_card_no'])){
						$yith_gift_card_no = $this->get_array_isset($payment_data,'yith_gift_card_no','',true);						
						$yith_gift_card_no_trim = $this->get_array_isset(array('yith_gift_card_no'=>$yith_gift_card_no),'yith_gift_card_no','',true,21);
						$Payment->setPaymentRefNum($yith_gift_card_no_trim);
					}
					
					$Payment->setTxnDate($_paid_date);

					//Payment Currency
					$qbo_home_currency = $this->get_qbo_company_setting('h_currency');
					if($_order_currency!='' && $qbo_home_currency!='' && $_order_currency!=$qbo_home_currency){

						$currency_rate_date = $_paid_date;
						$currency_rate = $this->get_qbo_cur_rate($_order_currency,$currency_rate_date,$qbo_home_currency);

						$Payment->setCurrencyRef($_order_currency);
						$Payment->setExchangeRate($currency_rate);
					}

					if($qb_p_method_id>0){
						 $Payment->setPaymentMethodRef($qb_p_method_id);
					}
					
					$dtar_acc_id = $qbo_account_id;
					
					/**/
					if(isset($payment_data['mw_qbo_yithwgcp']) && $payment_data['mw_qbo_yithwgcp']){
						$yithwgcp_gcp_qb_acc = (int) $this->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_acc');
						if($yithwgcp_gcp_qb_acc > 0){
							if(isset($payment_data['mw_qbo_yith_full_gift_paid']) || (isset($payment_data['mw_qbo_yith_partial_gift_paid']) && isset($payment_data['mw_qbo_yith_partial_gift_payment_add']))){
								$dtar_acc_id = $yithwgcp_gcp_qb_acc;
							}
						}
					}
					
					$Payment->setTotalAmt($payment_amount);

					$Line = new QuickBooks_IPP_Object_Line();
					$Line->setAmount($payment_amount);

					$LinkedTxn = new QuickBooks_IPP_Object_LinkedTxn();
					$LinkedTxn->setTxnId($qbo_invoice_id);
					$LinkedTxn->setTxnType('Invoice');
					$Line->setLinkedTxn($LinkedTxn);
					$Payment->addLine($Line);

					$Payment->setCustomerRef("{-$qbo_customer_id}");

					$Payment->setDepositToAccountRef("{-$dtar_acc_id}");
					
					/**/
					if(isset($payment_data['mw_qbo_yithwgcp']) && $payment_data['mw_qbo_yithwgcp']){
						$qb_p_method_id = (int) $this->get_option('mw_wc_qbo_sync_compt_yithwgcp_gcp_qb_pmethod');
						if($qb_p_method_id > 0){
							$Payment->setPaymentMethodRef($qb_p_method_id);
						}
					}
					
					//Add payment log
					$log_title = "";
					$log_details = "";
					$log_status = 0;
					
					//$this->_p($payment_data);
					//$this->_p($Payment);
					//return false;

					if ($resp = $PaymentService->add($Context, $realm, $Payment)){
						$qbo_pmnt_id = $this->qbo_clear_braces($resp);
						$log_title.="Export Payment Order #$ord_id_num\n";
						$log_details.="Payment for Order #$ord_id_num has been exported, QuickBooks Payment ID is #$qbo_pmnt_id";
						$log_status = 1;
						$this->save_payment_id_map($payment_id,$qbo_pmnt_id,1);
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Order Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse(),true);
						return $qbo_pmnt_id;

					}else{
						$res_err = $PaymentService->lastError($Context);
						$log_title.="Export Payment Error Order #$ord_id_num\n";
						$log_details.="Error:$res_err";
						$this->save_log($log_title,$log_details,'Payment',$log_status,true,'Add');
						$this->add_qbo_item_obj_into_log_file('Order Payment Add',$payment_data,$Payment,$this->get_IPP()->lastRequest(),$this->get_IPP()->lastResponse());
						return false;
					}
				}else{
					$this->save_log('Export Payment Error Order #'.$ord_id_num,'Payment sync not enabled for the gateway or invalid payment amount.','Payment',0);
					return false;
				}
			}
		}
	}
	
	return false;
}