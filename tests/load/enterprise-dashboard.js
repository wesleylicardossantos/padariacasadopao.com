import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 20,
  duration: '30s',
};

export default function () {
  const res = http.get('http://localhost:8080/enterprise/bi/dashboard?empresa_id=1', {
    headers: { 'X-Empresa-Id': '1' },
  });

  check(res, {
    'status 200 ou 302': (r) => [200, 302].includes(r.status),
  });

  sleep(1);
}
