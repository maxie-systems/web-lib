<?php

namespace MaxieSystems\URL;

enum PathType
{
    case Absolute;
    case Rootless;
    case Empty;
}
