#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
5sim API客户端封装
提供接码平台的API调用功能
"""

import time
import requests
from typing import Optional, Dict, Any
from loguru import logger


class FiveSimApi:
    """5sim接码平台API客户端"""
    
    def __init__(self, token: str):
        """
        初始化5sim API客户端
        
        Args:
            token: 5sim API token
        """
        self.token = token
        self.base_url = "https://5sim.net/v1"
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Accept': 'application/json'
        }
        
    def _request(self, method: str, endpoint: str, **kwargs) -> Optional[Dict[str, Any]]:
        """
        发送API请求
        
        Args:
            method: HTTP方法
            endpoint: API端点
            **kwargs: requests参数
            
        Returns:
            API响应数据或None
        """
        url = f"{self.base_url}{endpoint}"
        
        try:
            response = requests.request(
                method=method,
                url=url,
                headers=self.headers,
                timeout=30,
                **kwargs
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                logger.error(f"API请求失败: {response.status_code} - {response.text}")
                return None
                
        except Exception as e:
            logger.error(f"API请求异常: {e}")
            return None
    
    def get_balance(self) -> Optional[float]:
        """
        获取账户余额
        
        Returns:
            账户余额或None
        """
        result = self._request('GET', '/user/profile')
        if result:
            return result.get('balance')
        return None
    
    def get_prices(self, country: str = None, product: str = None) -> Optional[Dict[str, Any]]:
        """
        获取价格信息
        
        Args:
            country: 国家代码
            product: 产品名称
            
        Returns:
            价格信息或None
        """
        endpoint = '/guest/prices'
        params = {}
        
        if country:
            params['country'] = country
        if product:
            params['product'] = product
            
        return self._request('GET', endpoint, params=params)
    
    def buy_activation(self, country: str, operator: str, product: str) -> Optional[Dict[str, Any]]:
        """
        购买激活号码
        
        Args:
            country: 国家代码 (如: russia, england)
            operator: 运营商代码 (如: any, virtual59)
            product: 产品代码 (如: redbook)
            
        Returns:
            订单信息包含id和phone，失败返回None
        """
        endpoint = f'/user/buy/activation/{country}/{operator}/{product}'
        
        logger.info(f"购买激活号码: 国家={country}, 运营商={operator}, 产品={product}")
        result = self._request('GET', endpoint)
        
        if result:
            logger.info(f"购买成功: 订单ID={result.get('id')}, 手机号={result.get('phone')}")
        else:
            logger.error("购买激活号码失败")
            
        return result
    
    def check_order(self, order_id: str) -> Optional[Dict[str, Any]]:
        """
        检查订单状态
        
        Args:
            order_id: 订单ID
            
        Returns:
            订单信息或None
        """
        endpoint = f'/user/check/{order_id}'
        return self._request('GET', endpoint)
    
    def finish_order(self, order_id: str) -> bool:
        """
        完成订单
        
        Args:
            order_id: 订单ID
            
        Returns:
            是否成功
        """
        endpoint = f'/user/finish/{order_id}'
        result = self._request('GET', endpoint)
        
        if result and result.get('status') == 'FINISHED':
            logger.info(f"订单完成: {order_id}")
            return True
        else:
            logger.error(f"完成订单失败: {order_id}")
            return False
    
    def cancel_order(self, order_id: str) -> bool:
        """
        取消订单
        
        Args:
            order_id: 订单ID
            
        Returns:
            是否成功
        """
        endpoint = f'/user/cancel/{order_id}'
        result = self._request('GET', endpoint)
        
        if result and result.get('status') == 'CANCELED':
            logger.info(f"订单已取消: {order_id}")
            return True
        else:
            logger.error(f"取消订单失败: {order_id}")
            return False
    
    def wait_for_sms(self, order_id: str, timeout: int = 300, check_interval: int = 10) -> Optional[str]:
        """
        等待短信验证码
        
        Args:
            order_id: 订单ID
            timeout: 超时时间(秒)
            check_interval: 检查间隔(秒)
            
        Returns:
            验证码或None
        """
        logger.info(f"等待短信验证码: 订单ID={order_id}, 超时={timeout}秒")
        
        start_time = time.time()
        
        while time.time() - start_time < timeout:
            order_info = self.check_order(order_id)
            
            if not order_info:
                logger.warning("获取订单信息失败")
                time.sleep(check_interval)
                continue
            
            status = order_info.get('status')
            logger.debug(f"订单状态: {status}")
            
            # 检查是否收到短信
            if status == 'RECEIVED':
                sms_list = order_info.get('sms', [])
                if sms_list:
                    # 提取验证码 (通常是短信中的数字)
                    sms_text = sms_list[0].get('text', '')
                    logger.info(f"收到短信: {sms_text}")
                    
                    # 从短信中提取验证码
                    import re
                    code_match = re.search(r'\b(\d{4,6})\b', sms_text)
                    if code_match:
                        code = code_match.group(1)
                        logger.info(f"提取到验证码: {code}")
                        return code
                    else:
                        logger.warning(f"未能从短信中提取验证码: {sms_text}")
                        return sms_text  # 返回原始短信内容
            
            elif status in ['CANCELED', 'TIMEOUT']:
                logger.error(f"订单状态异常: {status}")
                return None
            
            # 等待下次检查
            elapsed = int(time.time() - start_time)
            logger.debug(f"等待验证码中... 已等待{elapsed}秒")
            time.sleep(check_interval)
        
        logger.error("等待验证码超时")
        return None
    
    def get_countries(self) -> Optional[Dict[str, Any]]:
        """
        获取支持的国家列表
        
        Returns:
            国家列表或None
        """
        return self._request('GET', '/guest/countries')
    
    def get_products(self) -> Optional[Dict[str, Any]]:
        """
        获取支持的产品列表
        
        Returns:
            产品列表或None
        """
        return self._request('GET', '/guest/products')


# 便捷函数
def create_fivesim_client(token: str) -> FiveSimApi:
    """
    创建5sim API客户端实例
    
    Args:
        token: 5sim API token
        
    Returns:
        FiveSimApi实例
    """
    return FiveSimApi(token)


if __name__ == "__main__":
    # 测试代码
    import os
    
    token = os.getenv("FIVESIM_TOKEN")
    if not token:
        print("请设置环境变量 FIVESIM_TOKEN")
        exit(1)
    
    api = FiveSimApi(token)
    
    # 测试获取余额
    balance = api.get_balance()
    print(f"账户余额: {balance}")
    
    # 测试获取价格
    prices = api.get_prices(country='england', product='redbook')
    print(f"价格信息: {prices}")