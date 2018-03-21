<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/26
 * Time: 10:22
 */

namespace worms\log;

interface Color
{
    const PINK = "\033[1;40;35m";
    const RED = "\033[1;40;31m";
    const YELLOW = "\033[1;40;33m";
    const GREEN = "\033[40;32m";
    const BLUE = "\033[1;40;36m";
    const GREY = "\033[1;40;30m";
    const END = "\033[0m";
}