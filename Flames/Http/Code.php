<?php

namespace Flames\Http;

class Code
{
    const int OK = 200;
    const int CREATED = 201;
    const int ACCEPTED = 202;
    const int NON_AUTHORITATIVE_INFORMATION = 203;
    const int NO_CONTENT = 204;
    const int RESET_CONTENT = 205;
    const int PARTIAL_CONTENT = 206;
    const int MULTIPLE_CHOICES = 300;
    const int MOVED_PERMANENTLY = 301;
    const int FOUND = 302;
    const int SEE_OTHER = 303;
    const int NOT_MODIFIED = 304;
    const int USER_PROXY = 305;
    const int UNUSED = 306;
    const int TEMPORARY_REDIRECT = 307;
    const int PERMANENT_REDIRECT = 308;
    const int BAD_REQUEST = 400;
    const int UNAUTHORIZED = 401;
    const int PAYMENT_REQUIRED = 402;
    const int FORBIDDEN = 403;
    const int NOT_FOUND = 404;
    const int METHOD_NOT_ALLOWED = 405;
    const int NOT_ACCEPTABLE = 406;
    const int PROXY_AUTHENTICATION_REQUIRED = 407;
    const int REQUEST_TIMEOUT = 408;
    const int CONFLICT = 409;
    const int GONE = 410;
    const int LENGTH_REQUIRED = 411;
    const int PRECONDITION_FAILED = 412;
    const int PAYLOAD_TOO_LARGE = 413;
    const int URI_TOO_LONG = 414;
    const int UNSUPPORTED_MEDIA_TYPE = 415;
    const int RANGE_NOT_SATISFIED = 416;
    const int EXPECTATION_FAILED = 417;
    const int MISDIRECTED_REQUEST = 421;
    const int TOO_EARLY = 425;
    const int UPGRADE_REQUIRED = 426;
    const int PRECONDITION_REQUIRED = 428;
    const int TOO_MANY_REQUESTS = 429;
    const int REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const int UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const int INTERNAL_SERVER_ERROR = 500;
    const int NOT_IMPLEMENTED = 501;
    const int BAD_GATEWAY = 502;
    const int SERVICE_UNAVAILABLE = 503;
    const int GATEWAY_TIMEOUT = 504;
    const int HTTP_VERSION_NOT_SUPPORTED = 505;
    const int VARIANT_ALSO_NEGOTIATES = 506;
    const int NOT_EXTENDED = 510;
    const int NETWORK_AUTHENTICATION_REQUIRED = 511;
}