<?php

namespace MaxieSystems;

enum URLType
{
    case Absolute;
    case Relative;
    case RootRelative;
    case ProtocolRelative;
}
