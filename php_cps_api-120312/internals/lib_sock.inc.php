<?php
	if (!defined("sock_timeout")) define("sock_timeout",60);

	CLASS SOCK
	{
		var $socket;
		var $error;
		var $errno;
		var $timeout;
	}

	function sock_init($timeout = sock_timeout)
	{
//		usleep(20000);
		$sock = new SOCK;
		unset($sock->socket);
		$sock->error = "";
		$sock->errno = 0;
		$sock->timeout = $timeout;
		return $sock;
	}

	function sock_open(&$sock, $host, $port)
	{
		global $TOTAL_SOCKETS;
		$TOTAL_SOCKETS++;
		$sock->error = "";
		$sock->errno = 0;
		$sock->timeout = 10;
		$sock->socket = @fsockopen($host,$port,$sock->error,$sock->errno,$sock->timeout);
		if ($sock->socket)
		{
			stream_set_timeout($sock->socket,$sock->timeout);
			return true;
		}
		else
			return false;
	}

	function sock_read(&$sock, $length = 1024)
	{
		$sock->error = "";
		$sock->errno = 0;
		$data = fread($sock->socket,$length);
		if (strlen($data)<$length)
		{
			$left = $length - strlen($data);
			$errs = 0;
			while ($left>0 && $errs<5)
			{
				$oldleft = $left;
				$part = fread($sock->socket,$left);
				if ($part!==false && strlen($part)>0)
				{
					$data .= $part;
					$left = $length - strlen($data);
				}
				elseif (sock_eof($sock))
					$left = 0;
				else
					$errs++;
			}
			if ($errs>=5)
			{
				$sock->error = "Socket read error";
				$sock->errno = 0;
			}
		}
		return $data;
	}

	function sock_write(&$sock, $data, $length = 0, $flush = false)
	{
		$sock->error = "";
		$sock->errno = 0;
		if (empty($length)) $length = strlen($data);

		$ret = false;
		$retries = 5;
		while (($ret === false || $ret < $length) && $retries > 0)
		{
			$ret += @fwrite($sock->socket,substr($data,$ret),$length-$ret);
			if ($ret === false || $ret < $length)
				usleep(500);
			$retries--;
		}

		if ($ret === false || $ret < $length)
		{
			obcmd_println("--> write error ($ret no $length [$retries])!");
			$sock->errno = 0;
			if ($ret<$length && !sock_eof($sock))
				$sock->error = "Socket write timeout";
			else
				$sock->error = "Socket write error";
		}
		else if ($flush)
			sock_flush($sock);
	}

	function sock_flush(&$sock)
	{
		$sock->error = "";
		$sock->errno = 0;
		fflush($sock->socket);
	}

	function sock_close(&$sock)
	{
		$sock->error = "";
		$sock->errno = 0;
		fclose($sock->socket);
	}

	function sock_free(&$sock)
	{
		$sock->error = "";
		$sock->errno = 0;
		unset($sock);
	}

	function sock_timeout(&$sock)
	{
		if ($sock->error=="Socket timeout" && $sock->errno==0)
			return true;
		else
			return false;
	}

	function sock_eof(&$sock)
	{
		return feof($sock->socket);
	}

	function sock_error(&$sock, &$error, &$errno)
	{
		if (!empty($sock->error) || !empty($sock->errno))
		{
			$error = $sock->error;
			$errno = $sock->errno;
			return true;
		}
		else
			return false;
	}
?>