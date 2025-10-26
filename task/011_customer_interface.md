# 011. Customer Interface Layout - TDD Implementation

## 개요
jiny/auth 패키지 기반 고객 포털 인터페이스 및 JWT 인증 통합

### jiny/auth 패키지 활용
- **JWT 인증 통합**: 기존 jiny/auth JWT 시스템 완전 활용
- **샤딩 지원**: users_0xx 테이블 기반 확장 가능한 사용자 관리
- **무상태 인증**: JWT 토큰 기반 확장 가능한 인증 시스템

## 의존관계
- **선행 태스크**: [010. 관리자 인터페이스](010_admin_interface.md)
- **후속 태스크**: [012. 파트너 인터페이스](012_partner_interface.md)

## 구현 체크리스트

### 고객 UI
- [ ] **서비스 카탈로그 브라우징** - HTTP 200 반환
- [ ] **구독 관리 대시보드** - HTTP 200 반환
- [ ] **무료 체험 관리** - HTTP 200 반환
- [ ] **청구 및 결제 관리** - HTTP 200 반환

### 완료 기준
- [ ] 모든 고객 페이지 HTTP 200 반환
- [ ] JWT 인증 완전 통합
- [ ] 모바일 최적화 완료

---

**이전 태스크**: [010. 관리자 인터페이스](010_admin_interface.md)
**다음 태스크**: [012. 파트너 인터페이스](012_partner_interface.md)