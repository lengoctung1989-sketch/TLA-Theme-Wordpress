# Snippet Playwright dùng cho repo này

Chạy trong tab local (http://localhost:8888). Nguyên tắc: **luôn in số ra**, không kết luận bằng mắt.

## 0. Bắt lỗi JS ngay từ đầu phiên

```js
page.on('pageerror', e => console.log('PAGEERROR', e.message));
page.on('console', m => { if (m.type() === 'error') console.log('CONSOLE', m.text()); });
```

## 1. Số đo phần tử + computed style

```js
const el = document.querySelector('.cp-cart-link');
const r = el.getBoundingClientRect();
const cs = getComputedStyle(el);
({ rect: { x: r.x, y: r.y, w: r.width, h: r.height, bottom: r.bottom },
   display: cs.display, width: cs.width, height: cs.height,
   color: cs.color, background: cs.backgroundColor, zIndex: cs.zIndex });
```

## 2. Rule nào đang áp dụng (đệ quy vào `@media` — quét phẳng sẽ KHÔNG thấy rule mobile)

```js
const el = document.querySelector('.cp-header .cp-logo .custom-logo');
const out = [];
(function walk(rule) {
  if (rule.cssRules && !rule.selectorText) { for (const r of rule.cssRules) walk(r); return; }
  if (rule.selectorText && el.matches(rule.selectorText)) out.push(rule.selectorText + ' { ' + rule.style.cssText + ' }');
})(null);
for (const sheet of document.styleSheets) {
  try { for (const r of sheet.cssRules) walk(r); } catch (e) { /* sheet cross-origin */ }
}
out;
```

> Quy tắc trong `@media` nằm ở `CSSMediaRule.cssRules` — phải đệ quy mới thấy. Đây là cách duy nhất để biết rule nào thực sự thắng.

## 3. Hit-test: phần tử nào thực sự nằm dưới chuột

```js
const el = document.querySelector('.cp-search-toggle');
const r = el.getBoundingClientRect();
[['tâm', r.x + r.width / 2, r.y + r.height / 2],
 ['trên', r.x + r.width / 2, r.y - 5],
 ['dưới', r.x + r.width / 2, r.bottom + 5],
 ['trái', r.x - 5, r.y + r.height / 2],
 ['phải', r.right + 5, r.y + r.height / 2]]
 .map(([n, x, y]) => [n, (document.elementFromPoint(x, y) || {}).className || '(null)']);
```

Nếu kết quả trả về phần tử khác (VD `img`, `div.cp-container`) → nút đang **bị đè**.

## 4. Không tràn ngang (chạy ở TỪNG bề rộng)

```js
({ innerWidth,
   scrollWidth: document.documentElement.scrollWidth,
   overflow: document.documentElement.scrollWidth > innerWidth });
```

## 5. Hover thật: di chuyển nhiều bước nhỏ, KHÔNG teleport

1. `await el.scrollIntoViewIfNeeded()` rồi kiểm `r.y >= 0 && r.bottom <= innerHeight`.
2. `mouse.move` từng bước ~4–8px từ ngoài vào đích để mô phỏng đường đi thật — teleport bỏ qua việc chuột đi xuyên vùng panel → **test PASS GIẢ**.
3. Đọc trạng thái ở 2 mốc: sau ~80ms (còn phải ẩn nếu có `transition-delay`) và sau ~480ms (phải hiện).

## 6. Phần tử có HIỆN hay không

Dùng `innerText` (hoặc `offsetParent !== null`, `rect.height > 0`). **Không** dùng `textContent` — nó đọc cả phần tử `display: none`.

## 7. Nhãn nút xuống mấy dòng

```js
// Cách đã dùng trong repo: tạo Range quanh phần chữ rồi đếm số dòng.
const range = document.createRange();
range.selectNodeContents(el);
({ lines: range.getClientRects().length,
   height: el.getBoundingClientRect().height,
   lineHeight: parseFloat(getComputedStyle(el).lineHeight) });
```

`lines > 1` (hoặc `height > lineHeight * 1.5`) ⇒ nhãn đã bị wrap xuống 2 dòng.

## 8. Ảnh / tài nguyên lỗi

```js
performance.getEntriesByType('resource')
  .filter(r => r.responseStatus >= 400 || r.transferSize === 0 && r.decodedBodySize === 0)
  .map(r => r.responseStatus + ' ' + r.name);
```

## 9. Bề rộng mẫu dùng thống nhất trong repo

`1440 · 1280 · 1100 · 1024 · 900 · 768 · 600 · 390` (thêm `320` khi kiểm logo/header mobile).

## 10. Cache

Thêm `?nc=<timestamp>` vào URL khi nghi ngờ cache (LiteSpeed trên production; local thì `filemtime()` đã lo cache-bust cho CSS/JS).
