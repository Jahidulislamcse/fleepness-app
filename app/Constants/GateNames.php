<?php

namespace App\Constants;

enum GateNames: string
{
    case CREATE_VENDOR = 'create-vendor';
    case CREATE_VENDOR_PRODUCT = 'create-vendor-product';
    case UPDATE_VENDOR_PRODUCT = 'update-vendor-product';
    case UPDATE_VENDOR = 'update-vendor';
    case FOLLOW_VENDOR = 'follow-vendor';
    case UNFOLLOW_VENDOR = 'unfollow-vendor';
    case CREATE_LIVESTREAM = 'create-livestream';
    case UPDATE_LIVESTREAM = 'update-livestream';
    case ADD_LIVESTREAM_PRODUCTS = 'add-livestream-products';
    case REMOVE_LIVESTREAM_PRODUCTS = 'remove-livestream-products';
    case GET_LIVESTREAM_PUBLISHER_TOKEN = 'get-livestream-publisher-token';
    case GET_LIVESTREAM_SUBSCRIBER_TOKEN = 'get-livestream-subscriber-token';
    case MAKE_ORDER_PICKUP_REQUEST = 'make-order-pickup-request';
}
