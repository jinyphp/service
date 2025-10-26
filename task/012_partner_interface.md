# 012. Partner Interface Layout - TDD Implementation

## 개요
jiny/auth 패키지 기반 파트너 대시보드 및 네트워크 관리 인터페이스

### jiny/auth 패키지 + 파트너 검증
- **JWT 기반 인증**: jiny/auth JWT 시스템 활용
- **파트너 검증 추가**: partner.verify 미들웨어로 파트너 자격 확인
- **샤딩 지원**: users_0xx + partners 테이블 연동

## 의존관계
- **선행 태스크**: [011. 고객 인터페이스](011_customer_interface.md)
- **후속 태스크**: [013. 종합 테스트](013_comprehensive_testing.md)

## 구현 체크리스트

### 파트너 UI
- [ ] **파트너 대시보드** - HTTP 200 반환
- [ ] **네트워크 관리** - HTTP 200 반환
- [ ] **커미션 추적** - HTTP 200 반환
- [ ] **서비스 할당 관리** - HTTP 200 반환

### 완료 기준
- [ ] 모든 파트너 페이지 HTTP 200 반환
- [ ] 파트너 검증 시스템 작동
- [ ] 성과 추적 시스템 완료

---

**이전 태스크**: [011. 고객 인터페이스](011_customer_interface.md)
**다음 태스크**: [013. 종합 테스트](013_comprehensive_testing.md)