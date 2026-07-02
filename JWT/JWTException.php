<?php

class JWTException extends Exception {}
class InvalidTokenException extends JWTException {}
class ExpiredTokenException extends JWTException {}
